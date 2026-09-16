<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Kontrakt to'lovining oylik "muddatlar jadvali" — Telegram bot, turniket
 * (Face ID) tekshiruvi va kunlik eslatma buyrug'i (SendPaymentReminders)
 * BARCHASI shu bitta xizmatdan foydalanadi, shuning uchun "bu oy uchun
 * qancha to'lash kerak edi" mantig'i faqat BITTA joyda yashaydi.
 *
 * QOIDA (Mukhtor tomonidan aniq belgilangan): o'quv yili sentyabrda
 * boshlanadi. Har oyning 20-sanasigacha kontraktning tegishli 1/8 ulushi
 * to'langan bo'lishi kerak — shunda keyingi oyning 1-sanasidan turniket
 * muammosiz ochiladi. Oxirgi (8-) muddat — 20-aprel, shu kunga kontrakt
 * TO'LIQ (8/8) yopilgan bo'lishi kerak.
 *
 * MUHIM: bu — Tutor KPI xizmatidagi (TutorKpiService) "oy o'tgani"
 * hisob-kitobidan ATAYLAB boshqacha va undan MUSTAQIL: KPI doimiy/yumshoq
 * o'sish sifatida (oy boshlanishi bilanoq shu oy uchun mas'uliyat
 * boshlanadi) hisoblanadi, bu yerda esa qattiq muddat (aynan 20-sana)
 * kerak — ikkalasi turli maqsadga xizmat qiladi, shuning uchun bitta
 * formulaga majburan birlashtirilmagan.
 */
class ContractPaymentScheduleService
{
    public const INSTALLMENT_MONTHS = 8;
    public const DEADLINE_DAY = 20;

    /**
     * Kontraktga tegishli, HOZIRDA o'qiyotgan talaba (Student) yozuvini
     * topadi.
     *
     * MUHIM: bu yerda `Contract::person` accessoridan ATAYLAB
     * foydalanilmaydi — u avval 'applicant'ni tekshiradi ($this->applicant
     * ?? $this->student), lekin abituriyentlik bosqichidan o'tgan
     * kontraktlarda 'applicant_id' to'ldirilgan bo'lsa-yu, 'student_id'
     * hali ham bo'sh bo'lishi mumkin (AdmissionSeeder buni har doim ham
     * to'ldirmaydi) — natijada `person` talaba ALLAQACHON mavjud bo'lsa
     * ham, uni emas, balki eski Applicant yozuvini qaytarib yuborar edi,
     * bu esa o'quv yili/guruh topilmay, muddat jadvali noto'g'ri
     * "noma'lum" chiqishiga olib kelardi. Shu sababli bu yerda to'g'ridan-
     * to'g'ri 'students' jadvalidan (avval student_id, topilmasa
     * applicant_id orqali) qidiriladi.
     */
    public function studentForContract(Contract $contract): ?Student
    {
        if ($contract->student_id) {
            return Student::find($contract->student_id);
        }

        if ($contract->applicant_id) {
            return Student::where('applicant_id', $contract->applicant_id)->first();
        }

        return null;
    }

    /**
     * Talabaning guruhi orqali o'quv yili boshlanish sanasini aniqlaydi —
     * Tutor KPI'da qabul qilingan konventsiya bilan bir xil ("guruh uchun
     * umumiy, Sentyabr boshlanishi"), guruh topilmasa talabaning o'z
     * 'academic_year_id'siga tushadi.
     */
    private function scheduleStartDate(Student $student): ?Carbon
    {
        $academicYear = $student->groups()->with('academicYear')->first()?->academicYear
            ?? $student->academicYear;

        return $academicYear?->start_date;
    }

    /**
     * 8 ta muddatning har biri: muddat sanasi (har oyning 20-sanasi) va shu
     * muddatgacha to'langan bo'lishi kerak bo'lgan JAMI (kumulyativ) summa.
     *
     * @return Collection<int, array{index:int, deadline:Carbon, required_amount:float}>
     */
    public function installmentSchedule(Contract $contract): Collection
    {
        $student = $this->studentForContract($contract);
        $start = $student ? $this->scheduleStartDate($student) : null;

        if (! $start) {
            return collect();
        }

        $amount = (float) $contract->amount;

        return collect(range(1, self::INSTALLMENT_MONTHS))->map(function (int $i) use ($start, $amount) {
            $deadline = $start->copy()->startOfMonth()->addMonths($i - 1)->day(self::DEADLINE_DAY)->startOfDay();

            // Oxirgi muddatda yumaloqlash xatosi tufayli 1-2 so'm kam
            // chiqib qolmasligi uchun aniq to'liq summa qo'yiladi.
            $required = $i === self::INSTALLMENT_MONTHS
                ? $amount
                : round($amount * $i / self::INSTALLMENT_MONTHS, 2);

            return ['index' => $i, 'deadline' => $deadline, 'required_amount' => $required];
        });
    }

    /**
     * Berilgan vaqt (odatda "hozir") holatiga ko'ra kontraktning to'lov
     * holati: necha-nchi muddat ichidamiz, shu muddatgacha qancha
     * to'langan bo'lishi kerak edi, haqiqatda qancha to'langan, qarz bor-
     * yo'qligi va keyingi muddat qachon.
     */
    public function currentStatus(Contract $contract, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $schedule = $this->installmentSchedule($contract);

        $paid = round((float) $contract->payments()->where('status', 'paid')->sum('amount'), 2);
        $totalAmount = (float) $contract->amount;
        $isFullyPaid = $paid >= $totalAmount - 1; // 1 so'mgacha yumaloqlash xatosiga tolerantlik

        if ($schedule->isEmpty()) {
            // O'quv yili/guruh ma'lum emas — muddat jadvalini hisoblab
            // bo'lmaydi. Ma'lumot yetishmasligi sababli talabani NOTO'G'RI
            // bloklab qo'ymaslik uchun "ruxsat berilgan" deb hisoblanadi,
            // lekin sabab aniq ko'rsatiladi (admin buni ko'rib tuzatishi
            // uchun).
            return [
                'known'                  => false,
                'current_index'          => null,
                'required_amount'        => null,
                'paid_amount'            => $paid,
                'debt_amount'            => 0.0,
                'is_compliant'           => true,
                'is_fully_paid'          => $isFullyPaid,
                'next_deadline'          => null,
                'next_required_amount'   => null,
                'is_compliant_for_next'  => true,
                'reason'                 => 'schedule_unknown',
            ];
        }

        $passed = $schedule->filter(fn (array $row) => $row['deadline']->lessThanOrEqualTo($now));
        $currentIndex = $passed->count(); // 0..8
        $requiredAmount = $currentIndex > 0 ? $passed->last()['required_amount'] : 0.0;
        $currentDeadline = $currentIndex > 0 ? $passed->last()['deadline'] : null;
        $nextRow = $schedule->firstWhere('index', $currentIndex + 1);

        $isCompliant = $isFullyPaid || $paid >= $requiredAmount - 1;

        // O'TGAN muddat (turniket/qarz nazorati uchun) bilan bir qatorda,
        // KELAYOTGAN muddat uchun ham alohida moslik belgisi kerak —
        // masalan, 1-muddatdan OLDIN (current_index=0, required_amount=0)
        // "is_compliant" har doim true bo'lib qoladi (haqiqatda qarz yo'q),
        // lekin bu talaba KELAYOTGAN 20-sanagacha hali to'lov qilmagan
        // bo'lishi mumkin — eslatma yuborish uchun aynan shu holat kerak.
        // Agar barcha muddatlar allaqachon o'tgan bo'lsa (nextRow yo'q),
        // "keyingi" talab — kontraktning to'liq summasi hisoblanadi.
        $nextRequiredAmount = $nextRow['required_amount'] ?? $totalAmount;
        $isCompliantForNext = $isFullyPaid || $paid >= $nextRequiredAmount - 1;

        return [
            'known'                  => true,
            'current_index'          => $currentIndex,
            'required_amount'        => $requiredAmount,
            'current_deadline'       => $currentDeadline,
            'paid_amount'            => $paid,
            'debt_amount'            => round(max(0, $requiredAmount - $paid), 2),
            'is_compliant'           => $isCompliant,
            'is_fully_paid'          => $isFullyPaid,
            'next_deadline'          => $isFullyPaid ? null : $nextRow['deadline'] ?? null,
            'next_required_amount'   => $isFullyPaid ? null : round($nextRequiredAmount, 2),
            'is_compliant_for_next'  => $isCompliantForNext,
            'reason'                 => $isCompliant ? null : 'debt',
        ];
    }

    /**
     * Talabaning (applicant_id yoki student_id orqali bog'langan) bekor
     * qilinmagan, kontrakt asosidagi shartnomasini topadi — Contract
     * qidiruvining ikkala yo'li ham (student_id VA applicant_id) tekshiriladi,
     * chunki AdmissionSeeder/eski yozuvlarda student_id har doim ham
     * to'ldirilmagan bo'lishi mumkin (TutorKpiService'dagi bilan bir xil
     * ehtiyot chorasi).
     */
    public function findActiveContract(Student $student): ?Contract
    {
        return Contract::where('payment_type', 'contract')
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($student) {
                $q->where('student_id', $student->id);
                if ($student->applicant_id) {
                    $q->orWhere('applicant_id', $student->applicant_id);
                }
            })
            ->latest('id')
            ->first();
    }
}
