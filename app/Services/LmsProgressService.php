<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;

/**
 * SCORM/xAPI kontenti o'zi "tugallandi"/"o'tdi" deb xabar berganda,
 * buni bizning o'z LessonProgress/Enrollment.progress hisoblagichimizga
 * ko'chiradi — shunda "Kurslarim" progress-bar va sertifikat mantig'i
 * oddiy (video/matn) darslar bilan SCORM/xAPI darslar uchun bir xil
 * ishlaydi (StudentCourseController::markComplete bilan bir xil mantiq).
 */
class LmsProgressService
{
    public function markLessonDone(Enrollment $enrollment, Lesson $lesson): void
    {
        $progress = LessonProgress::firstOrNew([
            'lesson_id' => $lesson->id,
            'user_id'   => $enrollment->user_id,
        ]);

        if ($progress->exists && $progress->is_completed) {
            return; // allaqachon tugallangan — qayta hisoblash shart emas
        }

        $progress->enrollment_id = $enrollment->id;
        $progress->is_completed = true;
        $progress->progress = 100;
        $progress->completed_at = now();
        $progress->save();

        $this->recalculateEnrollmentProgress($enrollment, $lesson);
    }

    private function recalculateEnrollmentProgress(Enrollment $enrollment, Lesson $lesson): void
    {
        $courseId = $lesson->module()->value('course_id');

        $totalLessons = Lesson::where('is_published', true)
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId)->where('is_published', true))
            ->count();

        $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        $enrollment->update([
            'progress'     => $percent,
            'status'       => $percent >= 100 ? 'completed' : $enrollment->status,
            'completed_at' => $percent >= 100 ? ($enrollment->completed_at ?? now()) : $enrollment->completed_at,
        ]);
    }
}
