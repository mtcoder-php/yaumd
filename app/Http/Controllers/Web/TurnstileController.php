<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ContractPaymentScheduleService;
use Illuminate\Http\Request;

/**
 * Turniket/Face ID tizimi uchun tekshiruv API'si — talaba yuzi
 * skanerlanganda turniket qurilmasi shu manzilga so'rov yuboradi va
 * "kirish mumkinmi/yo'qmi" javobini oladi.
 *
 * MUHIM: bu API hozircha ENG KO'P TARQALGAN umumiy shakl bo'yicha
 * qurilgan (JSHSHIR/HEMIS ID/talaba raqami orqali qidirish, Bearer token
 * bilan himoya) — SIZNING aniq turniket tizimingizning haqiqiy so'rov/
 * javob formati (masalan qanday maydon nomlari kutiladi, GET yoki POST,
 * qaysi identifikator yuboriladi) ma'lum bo'lgach, shu kontrollerni aynan
 * o'sha formatga moslab tuzatish kerak bo'ladi — buni ayting.
 *
 * Xavfsizlik: Click/Payme callback'lari kabi bu ham login talab qilmaydi
 * va CSRF tekshiruvidan ozod (bootstrap/app.php), o'rniga o'zining Bearer
 * token tekshiruvi bor ('TURNSTILE_API_TOKEN' — faqat turniket
 * qurilmasi/serveri biladigan maxfiy kalit).
 */
class TurnstileController extends Controller
{
    private const IDENTIFIER_COLUMNS = [
        'jshshir'        => 'jshshir',
        'hemis_id'       => 'hemis_id',
        'student_number' => 'student_number',
        'passport'       => 'passport_series',
    ];

    public function __construct(private ContractPaymentScheduleService $schedule)
    {
    }

    public function checkAccess(Request $request)
    {
        if (! $this->hasValidToken($request)) {
            return response()->json(['allowed' => false, 'reason' => 'unauthorized'], 401);
        }

        $data = $request->validate([
            'identifier_type' => 'required|string|in:' . implode(',', array_keys(self::IDENTIFIER_COLUMNS)),
            'identifier'      => 'required|string',
        ]);

        $column = self::IDENTIFIER_COLUMNS[$data['identifier_type']];
        $student = Student::where($column, $data['identifier'])->first();

        if (! $student) {
            return response()->json([
                'allowed' => false,
                'reason'  => 'not_found',
                'message' => 'Talaba topilmadi',
            ]);
        }

        if ($student->status !== 'active') {
            return response()->json([
                'allowed'      => false,
                'reason'       => 'inactive',
                'student_name' => $student->fullName(),
                'message'      => "Talaba holati faol emas ({$student->status})",
            ]);
        }

        if ($student->funding_type === 'grant') {
            return response()->json([
                'allowed'      => true,
                'reason'       => null,
                'student_name' => $student->fullName(),
                'message'      => 'Grant asosida o\'qiydi, kontrakt to\'lovi talab qilinmaydi',
            ]);
        }

        $contract = $this->schedule->findActiveContract($student);

        if (! $contract) {
            // Ma'lumotlar bazasida nomuvofiqlik (kontrakt turi "contract",
            // lekin haqiqiy kontrakt yozuvi topilmadi) — talabani noto'g'ri
            // bloklamaslik uchun ruxsat beriladi, lekin sabab aniq
            // ko'rsatiladi (admin buni ko'rib chiqishi kerak).
            return response()->json([
                'allowed'      => true,
                'reason'       => 'no_contract_found',
                'student_name' => $student->fullName(),
                'message'      => 'Diqqat: kontrakt yozuvi topilmadi (admin tekshirishi kerak)',
            ]);
        }

        $status = $this->schedule->currentStatus($contract);
        $allowed = $status['is_compliant'] || $status['is_fully_paid'];

        return response()->json([
            'allowed'         => $allowed,
            'reason'          => $allowed ? null : $status['reason'],
            'student_name'    => $student->fullName(),
            'debt_amount'     => $status['debt_amount'],
            'required_amount' => $status['required_amount'],
            'paid_amount'     => $status['paid_amount'],
            'next_deadline'   => $status['next_deadline']?->toDateString(),
            'message'         => $allowed
                ? 'Qarzi yo\'q, kirish ruxsat etiladi'
                : 'To\'lovda muammo bor: ' . number_format($status['debt_amount'], 0, '.', ' ') . " so'm qarz",
        ]);
    }

    private function hasValidToken(Request $request): bool
    {
        $expected = (string) config('services.turnstile.api_token');

        if ($expected === '') {
            return false;
        }

        $given = (string) $request->bearerToken();

        return $given !== '' && hash_equals($expected, $given);
    }
}
