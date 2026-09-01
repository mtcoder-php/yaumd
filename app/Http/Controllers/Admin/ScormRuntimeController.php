<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\ScormAttempt;
use App\Services\LmsProgressService;
use Illuminate\Http\Request;

/**
 * Talaba tomonidagi SCORM pleyeri (resources/js/scormApi.js) Commit()
 * yoki Finish()/Terminate() chaqirganda joriy CMI ma'lumotlar to'plamini
 * shu yerga yuboradi. Har doim joriy foydalanuvchining shu kursga
 * yozilganligi (Enrollment) bilan cheklanadi — boshqa birovning
 * urinishi hech qachon ko'rinmaydi/o'zgartirilmaydi.
 */
class ScormRuntimeController extends Controller
{
    public function __construct(private LmsProgressService $progress)
    {
    }

    public function commit(Request $request, int $courseId, int $lessonId)
    {
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $lesson = Lesson::with('scormPackage')
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId))
            ->findOrFail($lessonId);

        abort_if(! $lesson->scormPackage, 404, "Bu dars SCORM paketi emas.");
        abort_if(in_array($lesson->scormPackage->version, ['xapi'], true), 404, "Bu dars xAPI paketi — SCORM emas.");

        $data = $request->validate([
            'attempt_id'        => 'required|string|max:100',
            'completion_status' => 'nullable|in:unknown,incomplete,completed,not_attempted',
            'success_status'    => 'nullable|in:unknown,passed,failed',
            'score_raw'         => 'nullable|numeric',
            'score_min'         => 'nullable|numeric',
            'score_max'         => 'nullable|numeric',
            'score_scaled'      => 'nullable|numeric|between:-1,1',
            'session_time'      => 'nullable|integer|min:0',
            'suspend_data'      => 'nullable|string',
            'interactions'      => 'nullable|array',
            'objectives'        => 'nullable|array',
        ]);

        $attempt = ScormAttempt::firstOrNew([
            'scorm_package_id' => $lesson->scormPackage->id,
            'lesson_id'        => $lesson->id,
            'user_id'          => $request->user()->id,
        ]);

        if (! $attempt->exists) {
            $attempt->attempt_id = $data['attempt_id'];
        }

        foreach (['completion_status', 'success_status'] as $field) {
            if (! empty($data[$field])) {
                $attempt->{$field} = $data[$field];
            }
        }
        foreach (['score_raw', 'score_min', 'score_max', 'score_scaled'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $attempt->{$field} = $data[$field];
            }
        }
        if (array_key_exists('suspend_data', $data)) {
            $attempt->suspend_data = $data['suspend_data'];
        }
        if (array_key_exists('interactions', $data)) {
            $attempt->interactions = $data['interactions'];
        }
        if (array_key_exists('objectives', $data)) {
            $attempt->objectives = $data['objectives'];
        }

        $sessionSeconds = $data['session_time'] ?? 0;
        $attempt->session_time = $sessionSeconds;
        $attempt->total_time = ($attempt->total_time ?? 0) + $sessionSeconds;
        $attempt->save();

        // SCORM 1.2'da "passed" ham "completed" ma'nosini beradi
        // (lesson_status bitta maydon) — shuning uchun ikkalasini ham
        // "tugallandi" deb hisoblaymiz.
        $isDone = in_array($attempt->completion_status, ['completed'], true)
            || in_array($attempt->success_status, ['passed'], true);

        if ($isDone) {
            $this->progress->markLessonDone($enrollment, $lesson);
        }

        return response()->json(['ok' => true]);
    }
}
