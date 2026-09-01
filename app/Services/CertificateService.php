<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\ScormAttempt;

/**
 * Talaba kursni 100% tugatganda (Enrollment.status === 'completed' bo'lib
 * qolganda) sertifikat chiqarish mantig'i. Bitta Enrollment uchun faqat
 * BITTA sertifikat chiqariladi (certificates jadvalidagi
 * unique(['course_id','user_id']) cheklovi ham buni ta'minlaydi) — shuning
 * uchun bu metod har safar (hatto progress qaytadan 100%ga yetganda ham)
 * xavfsiz chaqirilishi mumkin.
 */
class CertificateService
{
    public function issueIfEligible(Enrollment $enrollment): void
    {
        if ($enrollment->status !== 'completed') {
            return;
        }

        // Kurs sozlamalarida sertifikat berish o'chirilgan bo'lishi mumkin.
        $course = $enrollment->course ?? $enrollment->course()->first();
        if (! $course || ! $course->has_certificate) {
            return;
        }

        if (Certificate::where('course_id', $enrollment->course_id)
            ->where('user_id', $enrollment->user_id)
            ->exists()) {
            return;
        }

        Certificate::create([
            'course_id'           => $enrollment->course_id,
            'user_id'             => $enrollment->user_id,
            'enrollment_id'       => $enrollment->id,
            'certificate_number'  => Certificate::generateNumber(),
            'final_score'         => $this->calculateFinalScore($enrollment),
            'issued_at'           => now(),
        ]);
    }

    /**
     * Yakuniy bahoni SCORM/xAPI urinishlaridagi ballar (score_raw)
     * o'rtachasi sifatida hisoblaydi. Talaba hech qanday baholanadigan
     * (SCORM/xAPI) dars topshirmagan bo'lsa — oddiy video/matn darslarni
     * ham to'liq tugatgani uchun standart 100.00 qo'yiladi.
     */
    private function calculateFinalScore(Enrollment $enrollment): float
    {
        $average = ScormAttempt::where('user_id', $enrollment->user_id)
            ->whereNotNull('score_raw')
            ->whereHas('lesson', fn ($q) => $q->whereHas(
                'module',
                fn ($qq) => $qq->where('course_id', $enrollment->course_id)
            ))
            ->avg('score_raw');

        return $average !== null ? round((float) $average, 2) : 100.00;
    }
}
