<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\ScormAttempt;
use App\Services\LmsProgressService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * "Kurslarim" — joriy foydalanuvchining o'ziga yozilgan (Enrollment)
 * kurslarini ko'rishi, darslarni ochishi va progressni belgilashi.
 *
 * MUHIM: bu yerdagi barcha so'rovlar har doim `auth()->id()` bilan
 * cheklanadi — boshqa birovning kursi/darsi ko'rinmaydi, hatto to'g'ridan-
 * to'g'ri URL orqali kirishga urinilsa ham (yozilmagan kurs/dars uchun 403).
 */
class StudentCourseController extends Controller
{
    public function __construct(
        private LmsProgressService $progress,
    ) {
    }

    public function index(Request $request): Response
    {
        $enrollments = Enrollment::where('user_id', $request->user()->id)
            ->with('course.category')
            ->latest('enrolled_at')
            ->get();

        return Inertia::render('Student/MyCourses/Index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $enrollment = $this->findEnrollmentOrFail($request, $id, [
            'course.modules' => fn ($q) => $q->where('is_published', true),
            'course.modules.lessons' => fn ($q) => $q->where('is_published', true),
            'certificate',
        ]);

        $completedLessonIds = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->pluck('lesson_id');

        return Inertia::render('Student/MyCourses/Show', [
            'enrollment'         => $enrollment,
            'completedLessonIds' => $completedLessonIds,
        ]);
    }

    public function lesson(Request $request, int $id, int $lessonId): Response
    {
        $enrollment = $this->findEnrollmentOrFail($request, $id, [
            'course.modules' => fn ($q) => $q->where('is_published', true)->orderBy('order'),
            'course.modules.lessons' => fn ($q) => $q->where('is_published', true)->orderBy('order'),
        ]);

        $lesson = Lesson::with(['video', 'attachments', 'scormPackage'])
            ->where('is_published', true)
            ->whereHas('module', fn ($q) => $q->where('course_id', $id)->where('is_published', true))
            ->findOrFail($lessonId);

        // Joriy urinishda darsni ochgani haqida belgi qo'yamiz (hali
        // tugallanmagan bo'lsa ham) — "Tugatdim" tugmasi bosilganda
        // is_completed=true qilinadi.
        LessonProgress::firstOrCreate(
            ['lesson_id' => $lessonId, 'user_id' => $request->user()->id],
            ['enrollment_id' => $enrollment->id]
        );

        // Oldingi/keyingi dars navigatsiyasi uchun kursning barcha
        // (nashr qilingan) darslarini tekis ro'yxatga tekislaymiz.
        $flatLessons = $enrollment->course->modules->flatMap->lessons->values();
        $currentIndex = $flatLessons->search(fn ($l) => $l->id === $lesson->id);

        $completedLessonIds = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->pluck('lesson_id');

        // SCORM (1.2/2004) bo'lsa — oldingi urinishni (davom ettirish/resume
        // uchun) beramiz. xAPI bo'lsa — pleyerga kerak bo'ladigan ishga
        // tushirish parametrlarini (endpoint/actor/registration) tayyorlaymiz.
        $scormAttempt = null;
        $xapiLaunch = null;

        if ($lesson->scormPackage && $lesson->scormPackage->version !== 'xapi') {
            $scormAttempt = ScormAttempt::where('lesson_id', $lesson->id)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        if ($lesson->scormPackage && $lesson->scormPackage->version === 'xapi') {
            $existingAttempt = ScormAttempt::where('lesson_id', $lesson->id)
                ->where('user_id', $request->user()->id)
                ->first();

            $xapiLaunch = [
                'endpoint'     => route('admin.my-courses.lessons.xapi.statements.store', [$id, $lessonId]),
                'actor'        => [
                    'objectType' => 'Agent',
                    'name'       => $request->user()->full_name,
                    'mbox'       => 'mailto:'.$request->user()->email,
                ],
                'registration' => $existingAttempt->attempt_id ?? (string) Str::uuid(),
                'activityId'   => $lesson->scormPackage->identifier,
            ];
        }

        return Inertia::render('Student/MyCourses/Lesson', [
            'enrollment'         => $enrollment,
            'lesson'             => $lesson,
            'completedLessonIds' => $completedLessonIds,
            'scormAttempt'       => $scormAttempt,
            'xapiLaunch'         => $xapiLaunch,
            'prevLesson'         => $currentIndex !== false && $currentIndex > 0
                ? ['id' => $flatLessons[$currentIndex - 1]->id, 'title_uz' => $flatLessons[$currentIndex - 1]->title_uz]
                : null,
            'nextLesson'         => $currentIndex !== false && $currentIndex < $flatLessons->count() - 1
                ? ['id' => $flatLessons[$currentIndex + 1]->id, 'title_uz' => $flatLessons[$currentIndex + 1]->title_uz]
                : null,
        ]);
    }

    public function markComplete(Request $request, int $id, int $lessonId)
    {
        $enrollment = $this->findEnrollmentOrFail($request, $id);

        $lesson = Lesson::whereHas('module', fn ($q) => $q->where('course_id', $id))
            ->findOrFail($lessonId);

        $wasCompleted = $enrollment->status === 'completed';

        // Progress/status hisob-kitobi va sertifikat chiqarish endi
        // LmsProgressService'da markazlashtirilgan — SCORM/xAPI Commit() va
        // oddiy "Tugatdim" tugmasi endi bir xil (va bir joydan
        // boshqariladigan) mantiqdan foydalanadi.
        $this->progress->markLessonDone($enrollment, $lesson);

        $justCompleted = ! $wasCompleted && $enrollment->fresh()->status === 'completed';

        return back()->with('success', $justCompleted
            ? "Tabriklaymiz! Kursni 100% tugatdingiz 🎉"
            : 'Dars tugallandi deb belgilandi!');
    }

    /**
     * Talaba o'ziga tegishli sertifikatni PDF holida yuklab oladi.
     * Sertifikat faqat kurs 100% tugatilgach (LmsProgressService /
     * CertificateService orqali) avtomatik yaratiladi — bu yerda hech
     * qanday yangi sertifikat yaratilmaydi, faqat mavjudi PDF qilinadi.
     */
    public function downloadCertificate(Request $request, int $id)
    {
        $enrollment = $this->findEnrollmentOrFail($request, $id, ['certificate', 'course', 'user']);

        $certificate = $enrollment->certificate;
        abort_if(! $certificate, 404, "Bu kurs uchun sertifikat hali chiqarilmagan.");

        $qrUrl = route('certificates.verify', $certificate->certificate_number);
        $qrCode = base64_encode(QrCode::format('svg')->size(200)->generate($qrUrl));

        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'user'        => $enrollment->user,
            'course'      => $enrollment->course,
            'qrCode'      => $qrCode,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("sertifikat-{$certificate->certificate_number}.pdf");
    }

    /**
     * Joriy foydalanuvchining shu kursga yozilganligini tekshiradi.
     * Yozilmagan bo'lsa — 404 emas, aynan 403 (taqiqlangan) qaytaradi,
     * chunki kurs mavjud, faqat unga huquq yo'q.
     *
     * @param  array<string, \Closure|null>  $with
     */
    private function findEnrollmentOrFail(Request $request, int $courseId, array $with = []): Enrollment
    {
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('user_id', $request->user()->id)
            ->when($with, fn ($q) => $q->with($with))
            ->first();

        abort_if(! $enrollment, 403, "Siz bu kursga yozilmagansiz.");

        return $enrollment;
    }
}
