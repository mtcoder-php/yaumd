<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Tutor (guruh rahbari) KPI hisob-kitobi.
 *
 * MUHIM — nima uchun oddiy "jami to'langan / jami kontrakt summasi" foizi
 * ISHLATILMAYDI: agar tutorga biriktirilgan 30 talabadan 10 tasi kontraktni
 * TO'LIQ (hatto oldindan) to'lab qo'ysa-yu, qolgan 20 tasi shu oy uchun
 * UMUMAN to'lov qilmasa — pul yig'indisi bo'yicha hisoblansa umumiy foiz
 * baribir 90%+ chiqib qolishi mumkin, garchi haqiqatda talabalarning
 * ko'pchiligi shu oy uchun hech narsa to'lamagan bo'lsa ham. Bu — vazifada
 * aniq ta'kidlangan "2-holat" (oldindan to'lash orqali ko'rsatkichni
 * "bo'yash"), va KPI shunday holatda BERILMASLIGI kerak.
 *
 * Shu sababli har bir TALABA alohida hisoblanadi: "hozirgi kungacha
 * to'lanishi kerak bo'lgan summa" (oy boshidan necha oy o'tgan bo'lsa,
 * kontraktning shuncha 1/8 ulushi) bilan "hozirgacha haqiqatda to'langan
 * summa" solishtiriladi, va foiz albatta 100% dan OSHIRILMAYDI (bitta
 * talaba butun kontraktni oldindan to'lab qo'ysa ham, uning shaxsiy
 * ko'rsatkichi 100% dan yuqori bo'lib boshqalarning 0%ini "yashira olmaydi").
 * Guruh/tutor darajasidagi foiz — shu talaba-darajasidagi (100%gacha
 * cheklangan) foizlarning oddiy o'rtachasi. Shunday qilib, ko'rsatkich
 * faqat HAQIQATDA ko'pchilik talaba shu oy uchun deyarli to'liq to'laganda
 * (vazifadagi "1-holat") 90%+ ga yeta oladi.
 */
class TutorKpiService
{
    // Talabalarga kontraktning umumiy summasini 8 oyga bo'lib to'lash
    // imkoni beriladi (chegirma qo'llangan bo'lsa ham — 'amount' allaqachon
    // chegirmadan keyingi haqiqiy summa, Contract modeliga qarang).
    public const INSTALLMENT_MONTHS = 8;

    // Shu foizdan (yoki undan yuqori) bo'lsa tutorga KPI berish mumkin.
    public const KPI_THRESHOLD_PERCENT = 90.0;

    /**
     * Barcha tutorlar bo'yicha qisqa xulosa — CRM'dagi umumiy ro'yxat
     * sahifasi uchun (Moliya/Super Admin).
     */
    public function summaryForAllTutors(): Collection
    {
        return User::role('tutor')
            ->orderBy('full_name')
            ->get()
            ->map(function (User $tutor) {
                $report = $this->reportForTutor($tutor);

                return [
                    'id'           => $tutor->id,
                    'full_name'    => $tutor->full_name,
                    'email'        => $tutor->email,
                    'groups_count' => $report['groups']->count(),
                    ...$report['summary'],
                ];
            })
            ->values();
    }

    /**
     * Bitta tutor uchun to'liq hisobot: har bir guruhi, har bir talabasi,
     * va umumiy KPI ko'rsatkichi. Ham CRM'dagi tafsilot sahifasida (boshqa
     * xodimlar uchun), ham tutorning o'zining "Mening KPI'm" sahifasida
     * ishlatiladi — ikkalasi ham AYNAN shu metodni chaqiradi, shu bilan
     * hisob-kitob mantig'i ikki joyda takrorlanmaydi.
     */
    public function reportForTutor(User $tutor): array
    {
        $groups = StudentGroup::where('tutor_id', $tutor->id)
            ->with('academicYear', 'direction')
            ->orderBy('name')
            ->get();

        $groupReports = $groups->map(fn (StudentGroup $group) => $this->groupReport($group));

        $allContractRows = $groupReports->flatMap(
            fn (array $g) => collect($g['students'])->filter(fn (array $r) => $r['has_contract'])
        );

        return [
            'tutor'   => [
                'id'        => $tutor->id,
                'full_name' => $tutor->full_name,
                'email'     => $tutor->email,
            ],
            'groups'  => $groupReports,
            'summary' => $this->aggregate($allContractRows),
        ];
    }

    private function groupReport(StudentGroup $group): array
    {
        $students = $group->students()
            ->get(['students.id', 'students.first_name', 'students.last_name', 'students.middle_name', 'students.applicant_id']);

        $monthsElapsed = $this->monthsElapsed($group);
        $contractByOwner = $this->contractsForStudents($students);

        $studentRows = $students->map(function (Student $student) use ($contractByOwner, $monthsElapsed) {
            $contract = $contractByOwner['byStudentId']->get($student->id)
                ?? ($student->applicant_id ? $contractByOwner['byApplicantId']->get($student->applicant_id) : null);

            $fullName = trim("{$student->last_name} {$student->first_name} {$student->middle_name}");

            if (!$contract) {
                return [
                    'id' => $student->id, 'full_name' => $fullName, 'has_contract' => false,
                ];
            }

            $dueToDate = round((float) $contract->amount / self::INSTALLMENT_MONTHS * $monthsElapsed, 2);
            $paidToDate = (float) ($contract->paid_sum ?? 0);
            // 100% dan OSHIRILMAYDI — muhim izohga qarang (fayl boshida).
            $percent = $dueToDate > 0 ? min(100.0, round($paidToDate / $dueToDate * 100, 1)) : 100.0;

            return [
                'id'              => $student->id,
                'full_name'       => $fullName,
                'has_contract'    => true,
                'contract_amount' => (float) $contract->amount,
                'due_to_date'     => $dueToDate,
                'paid_to_date'    => $paidToDate,
                'percent'         => $percent,
            ];
        });

        return [
            'id'             => $group->id,
            'name'           => $group->name,
            'academic_year'  => $group->academicYear?->name,
            'direction'      => $group->direction?->name_uz,
            'months_elapsed' => $monthsElapsed,
            'students_count' => $students->count(),
            'summary'        => $this->aggregate($studentRows->filter(fn (array $r) => $r['has_contract'])->values()),
            'students'       => $studentRows->values(),
        ];
    }

    /**
     * Kontrakt Abituriyentlar oqimi orqali (applicant_id) yoki talaba
     * to'g'ridan-to'g'ri kiritilganda (student_id) yaratilishi mumkin
     * (Contract::person accessoriga qarang — bu app bo'ylab takrorlanuvchi
     * naqsh) — shuning uchun har bir talabaning kontraktini ikkala yo'l
     * bilan ham qidiramiz, N+1 so'rovlarning oldini olish uchun bitta
     * so'rovda.
     */
    private function contractsForStudents(Collection $students): array
    {
        $studentIds = $students->pluck('id');
        $applicantIds = $students->pluck('applicant_id')->filter()->values();

        $contracts = Contract::where('payment_type', 'contract')
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($studentIds, $applicantIds) {
                $q->whereIn('student_id', $studentIds);
                if ($applicantIds->isNotEmpty()) {
                    $q->orWhereIn('applicant_id', $applicantIds);
                }
            })
            ->withSum(['payments as paid_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->get();

        return [
            'byStudentId'   => $contracts->whereNotNull('student_id')->keyBy('student_id'),
            'byApplicantId' => $contracts->whereNotNull('applicant_id')->keyBy('applicant_id'),
        ];
    }

    private function aggregate(Collection $contractRows): array
    {
        $count = $contractRows->count();

        if ($count === 0) {
            return [
                'contract_students_count' => 0,
                'average_percent'         => null,
                'kpi_eligible'            => false,
            ];
        }

        $average = round($contractRows->avg('percent'), 1);

        return [
            'contract_students_count' => $count,
            'average_percent'         => $average,
            'kpi_eligible'            => $average >= self::KPI_THRESHOLD_PERCENT,
        ];
    }

    /**
     * Guruhning o'quv yili boshlanish sanasidan hozirgacha nechanchi
     * "to'lov oyi" ekanligini hisoblaydi (1..8 oralig'ida cheklangan).
     */
    private function monthsElapsed(StudentGroup $group): int
    {
        $start = $group->academicYear?->start_date;

        if (!$start) {
            // O'quv yili ma'lum bo'lmasa, eng "og'ir" holatni olamiz —
            // butun jadval o'tgan deb hisoblanadi (kam baholanmasin).
            return self::INSTALLMENT_MONTHS;
        }

        $now = now();

        if ($start->greaterThan($now)) {
            return 1;
        }

        // MUHIM: Carbon::diffInMonths() ba'zan float qaytaradi (masalan
        // 1.34) — funksiyaning qaytish turi 'int' bo'lgani uchun buni
        // to'g'ridan-to'g'ri qo'shib yuborish PHP 8.1+ da "implicit
        // conversion from float to int loses precision" ogohlantirishini
        // chiqarardi. (int) bilan aniq kesib olinadi (to'liq o'tmagan oy
        // hisobga olinmaydi — bu "og'ir" tomonga emas, balki talabalarga
        // foyda beruvchi tomonga xato, chunki kamroq oy = kamroq "kerak
        // bo'lgan summa").
        $months = (int) $start->diffInMonths($now) + 1;

        return max(1, min(self::INSTALLMENT_MONTHS, $months));
    }
}
