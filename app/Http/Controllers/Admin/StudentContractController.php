<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Student;
use App\Services\ContractPdfService;
use Illuminate\Http\Request;
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
    public function show(Request $request): Response
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        // "student_id" — ApplicantController'da abituriyent talabaga
        // aylantirilganda avtomatik to'ldiriladi (yoki talaba to'g'ridan-
        // to'g'ri kiritilganda/import qilinganda darhol beriladi) — shu
        // sababli har doim shu ustun orqali qidirish yetarli va ishonchli.
        $contract = $student
            ? Contract::where('student_id', $student->id)
                ->with(['direction.faculty', 'payments' => fn ($q) => $q->latest()])
                ->first()
            : null;

        if (! $contract) {
            return Inertia::render('Student/Contract/Show', ['contract' => null]);
        }

        $paidAmount = (float) $contract->payments->where('status', 'paid')->sum('amount');
        $totalAmount = (float) $contract->amount;
        $remaining = max(0, $totalAmount - $paidAmount);

        return Inertia::render('Student/Contract/Show', [
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
}
