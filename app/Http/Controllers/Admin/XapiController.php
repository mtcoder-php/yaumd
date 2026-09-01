<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\ScormAttempt;
use App\Models\XapiStatement;
use App\Services\LmsProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * "Amaliy" xAPI (Tin Can) statement qabul qiluvchi — rasmiy Learning
 * Record Store spetsifikatsiyasining to'liq konformansi (Basic Auth
 * tekshiruvi, Activity State/Profile resurslari va h.k.) emas, lekin
 * real xAPI paketlaridan (Articulate Rise/Storyline eksporti va
 * shunga o'xshash) to'g'ridan-to'g'ri kelayotgan statement'larni qabul
 * qilib, ular asosida tugallanish/ball holatini kuzatish uchun yetarli.
 *
 * MUHIM: bu yerga faqat bizning ilovamiz ichidagi iframe orqali, bir xil
 * domendan kirilgani uchun, autentifikatsiya "Authorization: Basic ..."
 * sarlavhasi orqali emas, balki joriy Laravel sessiyasi (login) orqali
 * amalga oshiriladi — shuning uchun bu marshrutlar CSRF tekshiruvidan
 * ozod qilingan (bootstrap/app.php'ga qarang), chunki paket ichidagi
 * xAPI kutubxonasi Laravel CSRF tokenini bilmaydi.
 */
class XapiController extends Controller
{
    public function __construct(private LmsProgressService $progress)
    {
    }

    public function store(Request $request, int $courseId, int $lessonId)
    {
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $lesson = Lesson::with('scormPackage')
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId))
            ->findOrFail($lessonId);

        abort_if(! $lesson->scormPackage, 404, "Bu dars xAPI paketi emas.");
        abort_if($lesson->scormPackage->version !== 'xapi', 404, "Bu dars SCORM paketi — xAPI emas.");

        $statement = $request->json()->all();
        if (empty($statement)) {
            $statement = $request->all();
        }
        abort_if(empty($statement), 400, "Bo'sh statement.");

        $verbId = (string) data_get($statement, 'verb.id', '');
        $result = data_get($statement, 'result', []);

        XapiStatement::create([
            'scorm_package_id'    => $lesson->scormPackage->id,
            'lesson_id'           => $lesson->id,
            'user_id'             => $request->user()->id,
            'statement_id'        => $request->query('statementId') ?? data_get($statement, 'id') ?? (string) Str::uuid(),
            'verb_id'             => $verbId ?: null,
            'verb_display'        => data_get($statement, 'verb.display.en-US', data_get($statement, 'verb.display.en')),
            'object_id'           => data_get($statement, 'object.id'),
            'result_completion'   => data_get($result, 'completion'),
            'result_success'      => data_get($result, 'success'),
            'result_score_scaled' => data_get($result, 'score.scaled'),
            'result_score_raw'    => data_get($result, 'score.raw'),
            'result_duration'     => data_get($result, 'duration'),
            'raw'                 => $statement,
        ]);

        // Umumiy "hozirgi holat"ni ScormAttempt jadvaliga ham ko'chiramiz —
        // SCORM bilan bir xil jadval, shunda progress hisoblash mantig'i
        // SCORM va xAPI uchun bir xil bo'ladi.
        $attempt = ScormAttempt::firstOrNew([
            'scorm_package_id' => $lesson->scormPackage->id,
            'lesson_id'        => $lesson->id,
            'user_id'          => $request->user()->id,
        ]);
        if (! $attempt->exists) {
            $attempt->attempt_id = (string) Str::uuid();
        }

        if (str_ends_with($verbId, '/completed') || data_get($result, 'completion') === true) {
            $attempt->completion_status = 'completed';
        }
        if (str_ends_with($verbId, '/passed') || data_get($result, 'success') === true) {
            $attempt->success_status = 'passed';
        } elseif (str_ends_with($verbId, '/failed') || data_get($result, 'success') === false) {
            $attempt->success_status = 'failed';
        }
        foreach (['raw' => 'score_raw', 'min' => 'score_min', 'max' => 'score_max', 'scaled' => 'score_scaled'] as $resultKey => $field) {
            $value = data_get($result, "score.{$resultKey}");
            if ($value !== null) {
                $attempt->{$field} = $value;
            }
        }
        $attempt->save();

        if ($attempt->completion_status === 'completed' || $attempt->success_status === 'passed') {
            $this->progress->markLessonDone($enrollment, $lesson);
        }

        return response()->json(['ok' => true], 200);
    }

    /**
     * Ba'zi xAPI pleyerlari davom ettirish (resume) uchun avvalgi
     * statement'larni GET orqali so'raydi. Soddalashtirilgan javob —
     * bo'sh ro'yxat qaytaramiz (ko'pchilik pleyer buni "yangi urinish"
     * sifatida qabul qilib, baribir davom etaveradi).
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json([]);
    }
}
