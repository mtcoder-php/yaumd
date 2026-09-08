<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Student;
use App\Services\CrmDebtorExportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRM — Qarzdorlar ro'yxati.
 *
 * "Qarzdor" — kontrakt asosida o'qiydigan (payment_type='contract'),
 * bekor qilinmagan, va to'langan summasi shartnoma summasidan kam bo'lgan
 * har qanday shaxs (abituriyent yoki talaba, Contract::person orqali).
 * Alohida jadval kerak emas — mavjud contracts/payments'dan hisoblanadi.
 */
class CrmDebtorController extends Controller
{
    public function index(Request $request): Response
    {
        $debtors = $this->debtorRows($request);

        $perPage = 20;
        $page = max(1, (int) $request->input('page', 1));

        $paginated = new LengthAwarePaginator(
            $debtors->forPage($page, $perPage)->values(),
            $debtors->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Crm/Debtors', [
            'debtors' => $paginated,
            'totals'  => [
                'count'  => $debtors->count(),
                'amount' => round($debtors->sum('remaining_amount'), 2),
            ],
            'filters' => $request->only(['search', 'sort']),
        ]);
    }

    public function export(Request $request, CrmDebtorExportService $exportService)
    {
        $bytes = $exportService->export($this->debtorRows($request));
        $filename = 'qarzdorlar_' . now()->format('Y_m_d_His') . '.xlsx';

        return response($bytes, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * index() va export() bir xil filtr/saralashni ishlatishi uchun —
     * qolgan qarzi > 0 bo'lgan barcha kontraktlarni tayyor massiv
     * shaklida qaytaradi.
     *
     * @return Collection<int, array>
     */
    private function debtorRows(Request $request): Collection
    {
        $rows = Contract::query()
            ->where('payment_type', 'contract')
            ->where('status', '!=', 'cancelled')
            ->with(['applicant', 'student', 'direction.faculty'])
            ->withSum(['payments as paid_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->get()
            ->map(function (Contract $contract) {
                $paid = (float) ($contract->paid_sum ?? 0);
                $remaining = round(max(0, (float) $contract->amount - $paid), 2);
                $person = $contract->person;

                return [
                    'id'               => $contract->id,
                    'contract_number'  => $contract->contract_number,
                    'status'           => $contract->status,
                    'signed_at'        => $contract->signed_at,
                    'amount'           => (float) $contract->amount,
                    'paid_amount'      => $paid,
                    'remaining_amount' => $remaining,
                    'direction'        => $contract->direction ? [
                        'name_uz' => $contract->direction->name_uz,
                        'faculty' => $contract->direction->faculty?->name_uz,
                    ] : null,
                    'person' => $person ? [
                        'type'      => $person instanceof Student ? 'student' : 'applicant',
                        'id'        => $person->id,
                        'full_name' => trim("{$person->last_name} {$person->first_name} {$person->middle_name}"),
                        'phone'     => $person->phone,
                    ] : null,
                ];
            })
            ->filter(fn (array $row) => $row['remaining_amount'] > 0)
            ->values();

        if ($request->filled('search')) {
            $search = mb_strtolower((string) $request->input('search'));
            $rows = $rows->filter(function (array $row) use ($search) {
                return str_contains(mb_strtolower($row['contract_number']), $search)
                    || str_contains(mb_strtolower($row['person']['full_name'] ?? ''), $search);
            })->values();
        }

        return match ($request->input('sort', 'remaining_desc')) {
            'remaining_asc'  => $rows->sortBy('remaining_amount')->values(),
            'signed_at_asc'  => $rows->sortBy('signed_at')->values(),
            'signed_at_desc' => $rows->sortByDesc('signed_at')->values(),
            default          => $rows->sortByDesc('remaining_amount')->values(),
        };
    }
}
