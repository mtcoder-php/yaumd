<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Shartnoma PDF faylini qurish. Admin ContractController (moliya/admin
 * xodimi tomonidan) va StudentContractController (talaba o'zining
 * shartnomasini yuklab olishi uchun) — ikkalasi ham shu servisga
 * murojaat qiladi, shu sababli PDF shabloni bilan ishlash mantig'i
 * (raqamni so'zga o'tkazish, QR kod, degree yorlig'i) FAQAT BIR MARTA
 * yoziladi.
 */
class ContractPdfService
{
    public function generate(Contract $contract)
    {
        // Kontrakt Abituriyentlar oqimi orqali (applicant) yoki talaba
        // to'g'ridan-to'g'ri kiritilganda (student) yaratilgan bo'lishi
        // mumkin — PDF shablon ikkalasi uchun ham bir xil maydon nomlaridan
        // (first_name, last_name, passport_series va h.k.) foydalanadi.
        $applicant = $contract->applicant ?? $contract->student;
        $direction = $contract->direction;

        abort_if(! $applicant, 404, 'Kontrakt uchun shaxs maʼlumotlari topilmadi.');

        // Applicant'da "education_type" (bachelor/master/transfer/second),
        // Student'da esa to'g'ridan-to'g'ri "degree" (bachelor/master) bor —
        // shablon uchun ikkalasini bitta belgiga tenglaymiz.
        $degreeLabel = ($contract->applicant?->education_type ?? $contract->student?->degree) === 'master'
            ? 'Magistr'
            : 'Bakalavr';

        $qrUrl = url('/contracts/'.$contract->contract_number);

        $qrCode1 = base64_encode(QrCode::format('svg')->size(200)->generate($qrUrl));
        $qrCode2 = base64_encode(QrCode::format('svg')->size(200)->generate($qrUrl));

        $amountInWords = $this->numberToWords((int) $contract->amount);

        return Pdf::loadView('pdf.contract', compact(
            'contract', 'applicant', 'direction', 'qrCode1', 'qrCode2', 'amountInWords', 'degreeLabel'
        ))->setPaper('a4', 'portrait');
    }

    private function numberToWords(int $number): string
    {
        $ones = ['', 'bir', 'ikki', 'uch', 'to\'rt', 'besh', 'olti', 'yetti', 'sakkiz', 'to\'qqiz',
            'o\'n', 'o\'n bir', 'o\'n ikki', 'o\'n uch', 'o\'n to\'rt', 'o\'n besh',
            'o\'n olti', 'o\'n yetti', 'o\'n sakkiz', 'o\'n to\'qqiz'];
        $tens = ['', '', 'yigirma', 'o\'ttiz', 'qirq', 'ellik', 'oltmish', 'yetmish', 'sakson', 'to\'qson'];

        if ($number === 0) {
            return 'nol';
        }
        if ($number < 0) {
            return 'minus '.$this->numberToWords(-$number);
        }

        $result = '';
        if ($number >= 1000000000) {
            $result .= $this->numberToWords((int) ($number / 1000000000)).' milliard ';
            $number %= 1000000000;
        }
        if ($number >= 1000000) {
            $result .= $this->numberToWords((int) ($number / 1000000)).' million ';
            $number %= 1000000;
        }
        if ($number >= 1000) {
            $result .= $this->numberToWords((int) ($number / 1000)).' ming ';
            $number %= 1000;
        }
        if ($number >= 100) {
            $result .= $ones[(int) ($number / 100)].' yuz ';
            $number %= 100;
        }
        if ($number >= 20) {
            $result .= $tens[(int) ($number / 10)].' ';
            $number %= 10;
        }
        if ($number > 0) {
            $result .= $ones[$number].' ';
        }

        return trim($result);
    }
}
