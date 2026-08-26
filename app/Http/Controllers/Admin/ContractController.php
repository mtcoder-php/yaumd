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
        $year   = date('Y');
        $count  = Contract::whereYear('created_at', $year)->count() + 1;
        $number = 'KNT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

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
}
