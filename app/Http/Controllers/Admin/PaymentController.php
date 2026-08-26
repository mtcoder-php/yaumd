<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Payment::with(['contract.applicant', 'user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contract.applicant', fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('passport_series', 'like', "%{$search}%")
            )->orWhere('transaction_id', 'like', "%{$search}%");
        }

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'filters'  => $request->only(['status', 'provider', 'search']),
            'stats'    => [
                'total'   => Payment::where('status', 'paid')->sum('amount'),
                'today'   => Payment::where('status', 'paid')->whereDate('paid_at', today())->sum('amount'),
                'pending' => Payment::where('status', 'pending')->count(),
                'count'   => Payment::where('status', 'paid')->count(),
            ],
            'activeContracts' => Contract::whereIn('status', ['draft', 'signed'])
                ->with('applicant')
                ->get()
                ->map(fn($c) => [
                    'id'              => $c->id,
                    'contract_number' => $c->contract_number,
                    'applicant_name'  => $c->applicant?->last_name . ' ' . $c->applicant?->first_name,
                ]),
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        Payment::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        // Kontraktni to'landi deb belgilash
        $contract  = Contract::findOrFail($request->contract_id);
        $totalPaid = Payment::where('contract_id', $contract->id)
            ->where('status', 'paid')
            ->sum('amount');

        if ($totalPaid >= $contract->amount) {
            $contract->update(['status' => 'paid']);
        }

        return back()->with('success', "To'lov qabul qilindi!");
    }

    public function destroy(int $id)
    {
        Payment::findOrFail($id)->delete();
        return back()->with('success', "To'lov o'chirildi!");
    }
}
