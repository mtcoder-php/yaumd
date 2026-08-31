<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Applicant;
use App\Models\Contract;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ContractController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Contract::with(['applicant', 'direction.faculty'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                    ->orWhereHas('applicant', fn($q) => $q
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('passport_series', 'like', "%{$search}%")
                    );
            });
        }

        return Inertia::render('Admin/Contracts/Index', [
            'contracts' => $query->paginate(20)->withQueryString(),
            'filters'   => $request->only(['status', 'payment_type', 'search']),
            'stats'     => [
                'total'     => Contract::count(),
                'draft'     => Contract::where('status', 'draft')->count(),
                'signed'    => Contract::where('status', 'signed')->count(),
                'paid'      => Contract::where('status', 'paid')->count(),
                'cancelled' => Contract::where('status', 'cancelled')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Contracts/Create', [
            'applicants' => Applicant::where('status', 'contracted')
                ->with('direction')
                ->get()
                ->map(fn($a) => [
                    'id'    => $a->id,
                    'name'  => "{$a->last_name} {$a->first_name}",
                    'passport' => $a->passport_series,
                    'direction_id' => $a->direction_id,
                    'direction' => $a->direction?->name_uz,
                    'amount' => $a->direction?->annual_fee ?? 0,
                ]),
        ]);
    }

    public function store(StoreContractRequest $request)
    {
        if (Contract::where('applicant_id', $request->applicant_id)->exists()) {
            return back()->withErrors([
                'applicant_id' => 'Bu abituriyentga kontrakt allaqachon mavjud!',
            ]);
        }

        do {
            $number = 'BK' . random_int(100000000, 999999999);
        } while (Contract::withTrashed()->where('contract_number', $number)->exists());

        Contract::create([
            ...$request->validated(),
            'contract_number' => $number,
            'status'          => 'draft',
        ]);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Kontrakt yaratildi!');
    }

    public function show(int $id): Response
    {
        $contract = Contract::with(['applicant.region', 'applicant.district', 'direction.faculty'])
            ->findOrFail($id);

        return Inertia::render('Admin/Contracts/Show', [
            'contract' => $contract,
        ]);
    }
    public function edit(int $id): Response
    {
        $contract = Contract::with(['applicant.region', 'applicant.district', 'direction.faculty'])
            ->findOrFail($id);

        return Inertia::render('Admin/Contracts/Edit', [
            'contract' => $contract,
        ]);
    }
    public function update(UpdateContractRequest $request, int $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update($request->validated());

        return back()->with('success', 'Kontrakt yangilandi!');
    }

    public function destroy(int $id)
    {
        Contract::findOrFail($id)->delete();
        return back()->with('success', "Kontrakt o'chirildi!");
    }



    public function generatePdf(int $id)
    {
        $contract  = Contract::with(['applicant.region', 'applicant.district', 'direction'])->findOrFail($id);
        $applicant = $contract->applicant;
        $direction = $contract->direction;

        // QR kod URL
        $qrUrl = url('/contracts/' . $contract->contract_number);

        // QR kod generatsiya (SVG)
        $qrCode1 = base64_encode(QrCode::format('svg')->size(200)->generate($qrUrl));
        $qrCode2 = base64_encode(QrCode::format('svg')->size(200)->generate($qrUrl));
        // Summani so'zda
        $amountInWords = $this->numberToWords((int) $contract->amount);

        $pdf = Pdf::loadView('pdf.contract', compact(
            'contract', 'applicant', 'direction', 'qrCode1', 'qrCode2', 'amountInWords'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("kontrakt-{$contract->contract_number}.pdf");
    }

    private function numberToWords(int $number): string
    {
        $ones = ['', 'bir', 'ikki', 'uch', 'to\'rt', 'besh', 'olti', 'yetti', 'sakkiz', 'to\'qqiz',
            'o\'n', 'o\'n bir', 'o\'n ikki', 'o\'n uch', 'o\'n to\'rt', 'o\'n besh',
            'o\'n olti', 'o\'n yetti', 'o\'n sakkiz', 'o\'n to\'qqiz'];
        $tens  = ['', '', 'yigirma', 'o\'ttiz', 'qirq', 'ellik', 'oltmish', 'yetmish', 'sakson', 'to\'qson'];

        if ($number === 0) return 'nol';
        if ($number < 0)   return 'minus ' . $this->numberToWords(-$number);

        $result = '';
        if ($number >= 1000000000) {
            $result .= $this->numberToWords((int)($number / 1000000000)) . ' milliard ';
            $number %= 1000000000;
        }
        if ($number >= 1000000) {
            $result .= $this->numberToWords((int)($number / 1000000)) . ' million ';
            $number %= 1000000;
        }
        if ($number >= 1000) {
            $result .= $this->numberToWords((int)($number / 1000)) . ' ming ';
            $number %= 1000;
        }
        if ($number >= 100) {
            $result .= $ones[(int)($number / 100)] . ' yuz ';
            $number %= 100;
        }
        if ($number >= 20) {
            $result .= $tens[(int)($number / 10)] . ' ';
            $number %= 10;
        }
        if ($number > 0) {
            $result .= $ones[$number] . ' ';
        }
        return trim($result);
    }
}
