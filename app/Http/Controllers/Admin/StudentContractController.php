<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Student;
use App\Services\ContractPaymentScheduleService;
use App\Services\ContractPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Talaba o'zining shartnomasi va to'lovlari (qancha to'lagan, qancha
 * qolgan) haqida ma'lumot olishi uchun. "Kurslarim"/"Kutubxona" bilan bir
 * xil naqsh: mustaqil, o'z-o'zini xizmat qiluvchi controller — admin
 * ContractController'idagi 'contract.view' ruxsati moliya/admin xodimlari
 * uchun, talaba o'zining shartnomasini ko'rish uchun bunday ruxsatga ega
 * bo'lishi shart emas, shu sababli bu yerga 'permission:' qo'yilmagan.
 */
class StudentContractController extends Controller
{
    public function __construct(private ContractPaymentScheduleService $schedule)
    {
    }

    public function show(Request $request): Response
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        // MUHIM: kontrakt ba'zan 'student_id' orqali emas, balki
        // abituriyentlik bosqichidan qolgan 'applicant_id' orqali bog'langan
        // bo'lishi mumkin (AdmissionSeeder buni har doim ham to'ldirmaydi) —
        // shuning uchun ikkalasi ham tekshiriladi (TutorKpiService/
        // ContractPaymentScheduleService'dagi bilan bir xil ehtiyot chorasi,
        // aks holda ba'zi talabalarga shartnomasi "topilmadi" bo'lib chiqib
        // qolar edi).
        $contract = $student
            ? Contract::where(function ($q) use ($student) {
                $q->where('student_id', $student->id);
                if ($student->applicant_id) {
                    $q->orWhere('applicant_id', $student->applicant_id);
                }
            })
                ->with(['direction.faculty', 'payments' => fn ($q) => $q->latest()])
                ->latest('id')
                ->first()
            : null;

        if (! $contract) {
            return Inertia::render('Student/Contract/Show', [
                'contract' => null,
                'telegram' => $this->telegramProps($student),
            ]);
        }

        $paidAmount = (float) $contract->payments->where('status', 'paid')->sum('amount');
        $totalAmount = (float) $contract->amount;
        $remaining = max(0, $totalAmount - $paidAmount);

        $scheduleStatus = $contract->payment_type === 'contract'
            ? $this->schedule->currentStatus($contract)
            : null;

        return Inertia::render('Student/Contract/Show', [
            'telegram' => $this->telegramProps($student),
            'schedule' => $scheduleStatus ? [
                'known'                 => $scheduleStatus['known'],
                'is_compliant'          => $scheduleStatus['is_compliant'],
                'required_amount'       => $scheduleStatus['required_amount'],
                'debt_amount'           => $scheduleStatus['debt_amount'],
                'next_deadline'         => $scheduleStatus['next_deadline']?->toDateString(),
                'paid_amount'           => $scheduleStatus['paid_amount'],
                // "is_compliant" faqat O'TGAN muddat bo'yicha qarz
                // yo'qligini bildiradi (masalan 1-muddatdan OLDIN u har
                // doim true) — talaba KELAYOTGAN muddat uchun hali yetarli
                // to'lamagan bo'lishi mumkinligini ham ko'rsatish uchun bu
                // ikkita maydon qo'shildi (Telegram botdagi /holat bilan
                // bir xil mantiq).
                'next_required_amount'  => $scheduleStatus['next_required_amount'],
                'is_compliant_for_next' => $scheduleStatus['is_compliant_for_next'],
            ] : null,
            'contract' => [
                'id'               => $contract->id,
                'contract_number'  => $contract->contract_number,
                'payment_type'     => $contract->payment_type,
                'status'           => $contract->status,
                'signed_at'        => $contract->signed_at,
                'amount'           => $totalAmount,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remaining,
                'paid_percent'     => $totalAmount > 0 ? round(min(100, $paidAmount / $totalAmount * 100), 1) : 0,
                'direction'        => $contract->direction ? [
                    'name_uz' => $contract->direction->name_uz,
                    'faculty' => $contract->direction->faculty?->name_uz,
                ] : null,
                'payments' => $contract->payments->map(fn ($p) => [
                    'id'         => $p->id,
                    'amount'     => (float) $p->amount,
                    'provider'   => $p->provider,
                    'status'     => $p->status,
                    'paid_at'    => $p->paid_at,
                    'created_at' => $p->created_at,
                ]),
            ],
        ]);
    }

    // Talaba o'zining shartnomasini PDF holida yuklab olishi uchun. Haqiqiy
    // ruxsat tekshiruvi: kontrakt AYNAN shu talabaning (auth foydalanuvchi)
    // student_id'siga tegishli bo'lishi shart — aks holda 404 (boshqa
    // birovning shartnomasini ID orqali taxmin qilib ochish imkonsiz).
    public function downloadPdf(Request $request, int $id, ContractPdfService $pdfService)
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();

        $contract = Contract::with(['applicant.region', 'applicant.district', 'student', 'direction'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        return $pdfService->generate($contract)->download("kontrakt-{$contract->contract_number}.pdf");
    }

    /**
     * Telegram botga ulanish uchun bir martalik kod generatsiya qiladi —
     * talaba shu kodni botga "/kod 123456" ko'rinishida yuboradi, YOKI
     * shu kod bilan tuzilgan chuqur havolani (deep link) bosadi.
     * AuthController'dagi login kodi bilan bir xil Cache-asosidagi naqsh,
     * faqat bu yerda teskari yo'nalishda ("kod -> talaba ID") saqlanadi,
     * chunki bot xabarni qabul qilganda hali qaysi foydalanuvchi ekanini
     * bilmaydi — faqat kodning o'zini biladi.
     *
     * MUHIM: qiymat {type, id} shaklida saqlanadi (oddiy int emas) —
     * chunki endi xodimlar (Admin\ProfileController::generateTelegramCode())
     * ham AYNAN SHU BOT orqali, AYNAN SHU cache naqshi bilan ulanadi;
     * TelegramWebhookController::linkByCode() shu "type" maydoniga qarab
     * Student yoki User (xodim) sifatida bog'laydi.
     */
    public function generateTelegramCode(Request $request)
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();

        $code = (string) random_int(100000, 999999);

        Cache::put("telegram-link-code.{$code}", ['type' => 'student', 'id' => $student->id], now()->addMinutes(15));

        $botUsername = config('services.telegram.bot_username');

        return back()->with('telegramCode', [
            'code'          => $code,
            'deep_link'     => $botUsername ? "https://t.me/{$botUsername}?start={$code}" : null,
            'expires_in'    => 15,
        ]);
    }

    /**
     * Talaba xato hisobga ulanib qolgan taqdirda o'zi uzib qo'yishi uchun.
     */
    public function unlinkTelegram(Request $request)
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();

        $student->telegram_chat_id = null;
        $student->telegram_linked_at = null;
        $student->save();

        return back()->with('success', "Telegram bot bilan bog'lanish uzildi.");
    }

    private function telegramProps(?Student $student): array
    {
        return [
            'linked'      => (bool) $student?->telegram_chat_id,
            'linked_at'   => $student?->telegram_linked_at,
            'bot_username' => config('services.telegram.bot_username'),
        ];
    }
}
