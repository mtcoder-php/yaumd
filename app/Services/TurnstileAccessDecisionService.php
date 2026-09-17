<?php

namespace App\Services;

use App\Models\PersonMatch;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Phase 4 (turniketni to'lov holatiga qarab avtomatik ochish/yopish)
 * uchun — HOZIRCHA FAQAT HISOBLASH, HECH QANDAY YOZISH YO'Q.
 *
 * Bu xizmat mavjud, allaqachon ishlatilayotgan (Telegram bot, kunlik
 * eslatma) 'ContractPaymentScheduleService'ning aynan O'SHA "is_compliant"
 * mantig'idan foydalanadi — ya'ni turniket qarori bilan botdagi/saytdagi
 * "qarzim bormi" ko'rsatkichi HAR DOIM bir xil bo'ladi, chunki ikkalasi
 * bitta manbadan (bitta formuladan) keladi.
 *
 * MUHIM: bu yerda hisoblangan "desired_enable" hali HECH QAYERGA
 * (terminalga) yozilmaydi — buni faqat 'turnstile:access-preview' buyrug'i
 * ko'rsatib beradi (dry-run). Terminalga haqiqatda yozadigan qism —
 * eski tizim bilan to'qnashuv yo'qligi 1-oktabrdan keyin tasdiqlangach,
 * ALOHIDA qadam sifatida qo'shiladi.
 */
class TurnstileAccessDecisionService
{
    public function __construct(
        private readonly ContractPaymentScheduleService $scheduleService,
    ) {
    }

    /**
     * Ism bo'yicha talaba sifatida moslashtirilgan (auto_matched/matched)
     * har bir employee_no uchun, kontrakt to'lov holatiga qarab,
     * turniketda Valid.enable QANDAY bo'lishi KERAKLIGINI hisoblaydi.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function decisionsForStudents(): Collection
    {
        return PersonMatch::query()
            ->whereIn('status', [PersonMatch::STATUS_AUTO_MATCHED, PersonMatch::STATUS_MATCHED])
            ->where('matchable_type', 'student')
            ->with('matchable')
            ->get()
            ->map(fn (PersonMatch $match) => $this->decisionForMatch($match))
            ->filter()
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decisionForMatch(PersonMatch $match): ?array
    {
        $student = $match->matchable;

        if (! $student instanceof Student) {
            return null;
        }

        $name = trim("{$student->last_name} {$student->first_name} {$student->middle_name}");

        // O'qishdan chetlashtirilgan/bitirgan/ko'chirilgan talaba —
        // to'lov holatidan qat'i nazar, kirish huquqi masalasi Phase 4
        // doirasidan tashqarida (bu alohida, ATAYLAB qaror talab qiladigan
        // masala — shuning uchun hozircha "hal qilinmagan" deb belgilab,
        // avtomatik qarorga aralashtirmaymiz).
        if (! in_array($student->status, ['active', 'academic_leave'], true)) {
            return [
                'employee_no' => $match->employee_no,
                'student_id' => $student->id,
                'name' => $name,
                'reason' => 'status_' . $student->status,
                'is_compliant' => null,
                'debt_amount' => null,
                'desired_enable' => null,
            ];
        }

        $contract = $this->scheduleService->findActiveContract($student);

        if (! $contract) {
            // Grant asosida o'qiydigan yoki hali shartnoma tuzilmagan —
            // qarz nazorati qo'llanilmaydi, kirish doim ruxsat etiladi.
            return [
                'employee_no' => $match->employee_no,
                'student_id' => $student->id,
                'name' => $name,
                'reason' => 'no_contract',
                'is_compliant' => true,
                'debt_amount' => 0.0,
                'desired_enable' => true,
            ];
        }

        $status = $this->scheduleService->currentStatus($contract);

        return [
            'employee_no' => $match->employee_no,
            'student_id' => $student->id,
            'name' => $name,
            'reason' => $status['reason'] ?? ($status['known'] ? null : 'schedule_unknown'),
            'is_compliant' => $status['is_compliant'],
            'debt_amount' => $status['debt_amount'] ?? 0.0,
            'desired_enable' => $status['is_compliant'],
        ];
    }
}
