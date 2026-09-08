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
        ]);
    }

    private function kpi(): array
    {
        $debts = $this->outstandingContractBalances();

        return [
            'students_total'           => Student::count(),
            // "Qarzdorlar ulushi" mezoni uchun to'g'ri maxraj — grant
            // asosidagi talabalar hech qachon qarzdor bo'la olmaydi, shuning
            // uchun ulush "jami talabalar"ga emas, aynan kontrakt asosida
            // o'qiydiganlarga nisbatan hisoblanishi kerak.
            'contract_students_total'  => Student::where('funding_type', 'contract')->count(),
            'debtors_count'            => $debts->count(),
            'debtors_amount'           => round($debts->sum(), 2),
            'paid_this_month'          => round(
                (float) Payment::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount'),
                2
            ),
            'communications_this_week' => CommunicationLog::where('occurred_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Bekor qilinmagan, kontrakt asosidagi shartnomalarning qolgan
     * qarzlari — CrmDebtorController'dagi bilan bir xil hisoblash mantig'i
     * (faqat bu yerda to'liq ro'yxat emas, KPI uchun sonlar kerak).
     */
    private function outstandingContractBalances()
    {
        return Contract::where('payment_type', 'contract')
            ->where('status', '!=', 'cancelled')
            ->withSum(['payments as paid_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->get()
            ->map(fn (Contract $c) => round(max(0, (float) $c->amount - (float) ($c->paid_sum ?? 0)), 2))
            ->filter(fn (float $remaining) => $remaining > 0)
            ->values();
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
