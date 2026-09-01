<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Sertifikat PDF'idagi QR kod orqali ochiladigan OMMAVIY (login talab
 * qilinmaydigan) tekshiruv sahifasi — istalgan kishi (masalan ish beruvchi)
 * sertifikat raqamini bilib, uning haqiqiyligini shu yerda tekshirishi
 * mumkin. Contract'dagi "/contracts/{number}" bilan bir xil naqsh.
 */
class CertificateVerifyController extends Controller
{
    public function show(string $number): Response
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('certificate_number', $number)
            ->first();

        return Inertia::render('Web/Certificate/Verify', [
            'found'       => (bool) $certificate,
            'certificate' => $certificate ? [
                'certificate_number' => $certificate->certificate_number,
                'student_name'       => $certificate->user?->full_name,
                'course_title'       => $certificate->course?->title_uz,
                'final_score'        => $certificate->final_score,
                'issued_at'          => optional($certificate->issued_at)->format('Y-m-d'),
            ] : null,
        ]);
    }
}
