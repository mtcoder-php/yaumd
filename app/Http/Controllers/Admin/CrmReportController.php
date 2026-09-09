<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Student;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRM — Hisobotlar/Dashboard (spetsifikatsiya 4.5: "Hisobotlar" va
 * "Dashboard" bandlari). "Semestr bo'yicha" band spetsifikatsiyada bor,
 * lekin loyihada semestr tushunchasi hali modellanmagan — shuning uchun
 * uning o'rniga mavjud "o'quv yili" (academic_year) bo'yicha taqsimot
 * ko'rsatiladi.
 */
class CrmReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Crm/Reports', [
            'kpi'                  => $this->kpi(),
            'byDirection'          => $this->byDirection(),
            'byCourse'             => $this->byCourse(),
            'byAcademicYear'       => $this->byAcademicYear(),
            'recentCommunications' => $this->recentCommunications(),
            // Chegirma va oylik to'lov tahlili (CRM so'rovi: qancha talaba
            // qarzdor, chegirmasiz/chegirmali summa farqi, oylik dinamika).
            'discountByReason'     => $this->discountByReason(),
            'discountByPercent'    => $this->discountByPercent(),
            'monthlyPayments'      => $this->monthlyPaymentTrend(),
        ]);
    }

    private function kpi(): array
    {
        $rows = $this->contractFinancials();
        $debtorRows = $rows->filter(fn (array $r) => $r['remaining'] > 0);

        // "Barchasi to'lasa" — chegirma qo'llangandan keyingi (net) haqiqiy
        // to'lanishi kerak bo'lgan jami summa; "gross" — xuddi shu
        // kontraktlar chegirmasiz bo'lganda qancha bo'lardi.
        $targetTotal    = round($rows->sum('amount'), 2);
        $grossPotential = round($rows->sum('base_amount'), 2);
        $discountTotal  = round(max(0, $grossPotential - $targetTotal), 2);

        return [
            'students_total'           => Student::count(),
            // "Qarzdorlar ulushi" mezoni uchun to'g'ri maxraj — grant
            // asosidagi talabalar hech qachon qarzdor bo'la olmaydi, shuning
            // uchun ulush "jami talabalar"ga emas, aynan kontrakt asosida
            // o'qiydiganlarga nisbatan hisoblanishi kerak.
            'contract_students_total'  => Student::where('funding_type', 'contract')->count(),
            'debtors_count'            => $debtorRows->count(),
            'debtors_amount'           => round($debtorRows->sum('remaining'), 2),
            'paid_this_month'          => round(
                (float) Payment::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount'),
                2
            ),
            'communications_this_week' => CommunicationLog::where('occurred_at', '>=', now()->subDays(7))->count(),

            // Kontrakt bo'yicha "hammasi to'lansa qancha bo'ladi" / chegirma tahlili
            'contract_target_total'     => $targetTotal,
            'collected_total'           => round($rows->sum('paid'), 2),
            'gross_potential_total'     => $grossPotential,
            'discount_amount_total'     => $discountTotal,
            'discount_percent_of_gross' => $grossPotential > 0 ? round($discountTotal / $grossPotential * 100, 1) : 0,
            'students_with_discount'    => $rows->filter(fn (array $r) => $r['discount_percent'] > 0)->count(),
        ];
    }

    /**
     * Bekor qilinmagan, kontrakt asosidagi har bir shartnoma uchun
     * summa/chegirma/to'lov ma'lumotlarini bitta joyda tayyorlaydi —
     * kpi(), discountByReason() va discountByPercent() shu massivdan
     * kelib chiqib turli kesimlarda hisob-kitob qiladi (bir xil so'rov
     * bir necha marta takrorlanmasligi uchun).
     *
     * @return \Illuminate\Support\Collection<int, array>
     */
    private function contractFinancials(): \Illuminate\Support\Collection
    {
        return Contract::where('payment_type', 'contract')
            ->where('status', '!=', 'cancelled')
            ->withSum(['payments as paid_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->get()
            ->map(function (Contract $c) {
                $paid = (float) ($c->paid_sum ?? 0);

                return [
                    'amount'           => (float) $c->amount,
                    // Eski yozuvlarda ham migratsiya orqali to'ldirilgan,
                    // lekin ehtiyot uchun fallback qoldiriladi.
                    'base_amount'      => (float) ($c->base_amount ?? $c->amount),
                    'discount_percent' => (int) $c->discount_percent,
                    'discount_reason'  => $c->discount_reason,
                    'paid'             => $paid,
                    'remaining'        => round(max(0, (float) $c->amount - $paid), 2),
                ];
            });
    }

    private function byDirection()
    {
        return Student::query()
            ->selectRaw('direction_id, COUNT(*) as student_count')
            ->groupBy('direction_id')
            ->with('direction:id,name_uz')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->direction?->name_uz ?? "Noma'lum",
                'count' => (int) $row->student_count,
            ])
            ->sortByDesc('count')
            ->values();
    }

    private function byCourse()
    {
        return Student::query()
            ->selectRaw('course_year, COUNT(*) as student_count')
            ->whereNotNull('course_year')
            ->groupBy('course_year')
            ->orderBy('course_year')
            ->get()
            ->map(fn ($row) => [
                'label' => "{$row->course_year}-kurs",
                'count' => (int) $row->student_count,
            ])
            ->values();
    }

    private function byAcademicYear()
    {
        return Student::query()
            ->selectRaw('academic_year_id, COUNT(*) as student_count')
            ->groupBy('academic_year_id')
            ->with('academicYear:id,name')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->academicYear?->name ?? "Noma'lum",
                'count' => (int) $row->student_count,
            ])
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Chegirma sababi bo'yicha taqsimot — nechta talaba, va shu sabab
     * bo'yicha jami qancha summa "chegirma qilib berilgan" (base - amount).
     */
    private function discountByReason()
    {
        $rows = $this->contractFinancials()->filter(fn (array $r) => $r['discount_percent'] > 0);

        return collect(Contract::DISCOUNT_REASONS)
            ->map(function (string $label, string $key) use ($rows) {
                $matched = $rows->filter(fn (array $r) => $r['discount_reason'] === $key);

                return [
                    'label'  => $label,
                    'count'  => $matched->count(),
                    'amount' => round($matched->sum(fn (array $r) => $r['base_amount'] - $r['amount']), 2),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Chegirma foizi (10/20/25/50/75/100%) bo'yicha nechta talabaga shu
     * foiz berilgani — "qancha foizi chegirma qilib berilmoqda" so'rovining
     * tarqalish (distribution) ko'rinishi.
     */
    private function discountByPercent()
    {
        $rows = $this->contractFinancials()->filter(fn (array $r) => $r['discount_percent'] > 0);

        return collect(array_slice(Contract::DISCOUNT_PERCENTS, 1))
            ->map(fn (int $percent) => [
                'label' => "{$percent}%",
                'count' => $rows->filter(fn (array $r) => $r['discount_percent'] === $percent)->count(),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();
    }

    /**
     * Oxirgi 12 oy uchun to'langan summalar va oldingi oyga nisbatan
     * o'zgarish foizi ("oylik to'lovlar solishtirilmasi... oshdi kamaydi").
     * Oy nomi frontendda formatlanadi (boshqa sahifalardagi kabi) — bu
     * yerda faqat "YYYY-MM" qaytariladi.
     */
    private function monthlyPaymentTrend(): array
    {
        $months = collect(range(11, 0))
            ->map(fn (int $i) => now()->copy()->subMonths($i)->startOfMonth());

        $amounts = $months->map(function ($month) {
            return (float) Payment::where('status', 'paid')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount');
        })->values();

        return $months->values()->map(function ($month, int $i) use ($amounts) {
            $amount = round($amounts[$i], 2);
            $prev = $i > 0 ? $amounts[$i - 1] : null;

            return [
                'month'          => $month->format('Y-m'),
                'amount'         => $amount,
                'change_percent' => ($prev !== null && $prev > 0)
                    ? round((($amounts[$i] - $prev) / $prev) * 100, 1)
                    : null,
            ];
        })->all();
    }

    private function recentCommunications()
    {
        return CommunicationLog::with(['creator:id,full_name', 'subject'])
            ->latest('occurred_at')
            ->limit(8)
            ->get()
            ->map(function (CommunicationLog $log) {
                $subject = $log->subject;

                return [
                    'id'           => $log->id,
                    'type'         => $log->type,
                    'summary'      => $log->summary,
                    'occurred_at'  => $log->occurred_at,
                    'creator'      => $log->creator?->full_name,
                    'subject_name' => $subject
                        ? trim("{$subject->last_name} {$subject->first_name}")
                        : "Noma'lum",
                ];
            });
    }
}
