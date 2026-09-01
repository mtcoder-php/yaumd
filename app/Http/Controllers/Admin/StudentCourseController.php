<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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

        $lesson = Lesson::with(['video', 'attachments'])
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

        return Inertia::render('Student/MyCourses/Lesson', [
            'enrollment'         => $enrollment,
            'lesson'             => $lesson,
            'completedLessonIds' => $completedLessonIds,
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

        LessonProgress::updateOrCreate(
            ['lesson_id' => $lesson->id, 'user_id' => $request->user()->id],
            [
                'enrollment_id' => $enrollment->id,
                'is_completed'  => true,
                'progress'      => 100,
                'completed_at'  => now(),
            ]
        );

        $totalLessons = Lesson::where('is_published', true)
            ->whereHas('module', fn ($q) => $q->where('course_id', $id)->where('is_published', true))
            ->count();

        $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;
        $justCompleted = $percent >= 100 && $enrollment->status !== 'completed';

        $enrollment->update([
            'progress'     => $percent,
            'status'       => $percent >= 100 ? 'completed' : $enrollment->status,
            'completed_at' => $percent >= 100 ? ($enrollment->completed_at ?? now()) : $enrollment->completed_at,
        ]);

        return back()->with('success', $justCompleted
            ? "Tabriklaymiz! Kursni 100% tugatdingiz 🎉"
            : 'Dars tugallandi deb belgilandi!');
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
