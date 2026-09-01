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
    public function __construct(
        private CertificateService $certificates,
    ) {
    }

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

        $courseId = $lesson->module()->value('course_id');
        $this->recalculateEnrollment($enrollment, $courseId);
    }

    /**
     * Bitta kursning BARCHA ro'yxatdan o'tishlari (Enrollment) uchun
     * progress-foizni qayta hisoblaydi. Dars o'chirilganda yoki
     * nashrdan olib tashlanganda albatta chaqirilishi shart — aks holda
     * "Enrollment.progress" o'sha voqeadan OLDINGI (endi noto'g'ri)
     * qiymatda "muzlab" qolaveradi (masalan, kursda 6 ta dars bo'lib,
     * 5 tasi tugallangan holda 4 tasi o'chirilsa, progress hamon eski
     * "83%" qiymatida qolib ketadi, garchi endi bor-yo'g'i 2 ta dars
     * qolgan bo'lsa ham).
     */
    public function recalculateCourseEnrollments(int $courseId): void
    {
        Enrollment::where('course_id', $courseId)->get()->each(
            fn (Enrollment $enrollment) => $this->recalculateEnrollment($enrollment, $courseId)
        );
    }

    private function recalculateEnrollment(Enrollment $enrollment, int $courseId): void
    {
        $totalLessons = Lesson::where('is_published', true)
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId)->where('is_published', true))
            ->count();

        // MUHIM: faqat HOZIR mavjud va nashr qilingan darslarga tegishli
        // "tugallangan" progress yozuvlari hisobga olinadi — aks holda
        // allaqachon o'chirilgan yoki nashrdan olingan darsning eski
        // "tugallangan" belgisi progressni haqiqatdan yuqori ko'rsatib
        // qolaveradi.
        $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->whereHas('lesson', fn ($q) => $q->where('is_published', true)
                ->whereHas('module', fn ($qq) => $qq->where('course_id', $courseId)->where('is_published', true)))
            ->count();

        $percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        // Agar kursga keyinchalik yangi (nashr qilingan) dars qo'shilsa va
        // talaba hali uni ko'rmagan bo'lsa, foiz 100 dan pastga tushadi —
        // bunday holda "tugallangan" holati ham haqiqatga mos ravishda
        // "faol"ga qaytariladi. DIQQAT: allaqachon chiqarilgan sertifikat
        // BEKOR QILINMAYDI (CertificateService faqat yaratadi, o'chirmaydi) —
        // bu ataylab shunday: talaba sertifikatni qo'lga kiritgach, keyinroq
        // kursga qo'shilgan yangi darsni ko'rmagani uchun uni "orqaga tortish"
        // noto'g'ri bo'lardi. Boshqa holatlar (masalan 'cancelled', 'expired')
        // tegilmaydi.
        $status = $enrollment->status;
        if ($percent >= 100) {
            $status = 'completed';
        } elseif ($status === 'completed') {
            $status = 'active';
        }

        $enrollment->update([
            'progress'     => $percent,
            'status'       => $status,
            'completed_at' => $percent >= 100
                ? ($enrollment->completed_at ?? now())
                : ($enrollment->status === 'completed' ? null : $enrollment->completed_at),
        ]);

        // Endigina "completed" holatiga o'tgan (yoki allaqachon shunday
        // bo'lgan, lekin sertifikati hali chiqarilmagan) yozilishlar uchun
        // sertifikat chiqaramiz. issueIfEligible() ichida allaqachon mavjud
        // sertifikat va has_certificate=false holatlari tekshiriladi, shu
        // sabab bu yerda qo'shimcha shart shart emas.
        if ($status === 'completed') {
            $this->certificates->issueIfEligible($enrollment->fresh('course'));
        }
    }
}
