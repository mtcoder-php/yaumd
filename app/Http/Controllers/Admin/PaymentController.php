<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Contract;
use App\Models\Payment;
use App\Services\ClickPaymentService;
use App\Services\PaymePaymentService;
use App\Services\ReceiptOcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        // MUHIM: kontrakt Abituriyentlar oqimi orqali (applicant_id) YOKI
        // talaba to'g'ridan-to'g'ri kiritilganda (student_id) yaratilishi
        // mumkin (Contract::$person accessoriga qarang, ContractController
        // ham har doim ikkalasini birga yuklaydi) — shu yerda faqat
        // 'applicant' yuklangani uchun talaba orqali kelgan to'lovlarda
        // ism-familiya bo'sh ko'rinib qolgan edi.
        $query = Payment::with(['contract.applicant', 'contract.student', 'user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            // Bitta yopiq guruh ichida — aks holda ->orWhere(...) tashqi
            // status/provider filtrlaridan ham "chiqib ketib", ularni
            // chetlab o'tgan bo'lardi.
            $query->where(function ($q) use ($search) {
                $q->whereHas('contract.applicant', fn($q2) => $q2
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('passport_series', 'like', "%{$search}%")
                )
                    ->orWhereHas('contract.student', fn($q2) => $q2
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('passport_series', 'like', "%{$search}%")
                    )
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
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
                ->with(['applicant', 'student'])
                ->get()
                ->map(function (Contract $c) {
                    $person = $c->applicant ?? $c->student;

                    return [
                        'id'              => $c->id,
                        'contract_number' => $c->contract_number,
                        'applicant_name'  => trim(($person?->last_name ?? '') . ' ' . ($person?->first_name ?? '')),
                    ];
                }),
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
        $payment = Payment::findOrFail($id);

        // Chek surati ham diskdan o'chiriladi — aks holda ishlatilmay
        // qolgan fayllar "public/storage/payments/receipts" papkasida
        // abadiy to'planib qolaverardi.
        if ($payment->receipt_path) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        $payment->delete();

        return back()->with('success', "To'lov o'chirildi!");
    }

    /**
     * Kassir "Bank cheki" turini tanlab, chekning suratini yuklaganda
     * chaqiriladi (to'lov hali SAQLANMAYDI) — fayl 'payments/receipts'
     * papkasiga joylanadi, so'ng ReceiptOcrService orqali OCR qilinib,
     * undan topilgan summa frontendga qaytariladi. Kassir shu summani
     * ko'rib, kerak bo'lsa tuzatib, "Qabul qilish" bosgandagina haqiqiy
     * Payment yozuvi (store()) yaratiladi — receipt_path o'sha so'rovda
     * yashirin maydon sifatida qayta yuboriladi.
     */
    public function scanReceipt(Request $request, ReceiptOcrService $ocr)
    {
        $request->validate([
            'receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ], [
            'receipt.required' => 'Chek suratini tanlang',
            'receipt.image'    => 'Fayl rasm formatida bo\'lishi kerak',
            'receipt.max'      => 'Rasm hajmi 10 MB dan katta bo\'lmasligi kerak',
        ]);

        $path = $request->file('receipt')->store('payments/receipts', 'public');

        $result = $ocr->scan(Storage::disk('public')->path($path));

        return response()->json([
            'receipt_path' => $path,
            'receipt_url'  => Storage::disk('public')->url($path),
            'amount'       => $result['amount'],
            'raw_text'     => $result['raw_text'],
        ]);
    }

    /**
     * Moliya xodimi kassada turgan holda talaba/abituriyent uchun HAQIQIY
     * Click/Payme to'lov havolasini (QR) generatsiya qiladi — talaba
     * o'zining shaxsiy kabinetidan foydalanadigan ContractPaymentController
     * bilan bir xil naqsh (Payment yozuvi 'pending' holatda yaratiladi,
     * haqiqiy tasdiq server-serverga keladigan callback orqali amalga
     * oshadi), farqi shundaki: (1) kontrakt talaba o'zi emas, kassir
     * tomonidan tanlanadi, (2) bu oddiy Inertia sahifa emas, balki modal
     * ichidan chaqiriladigan JSON so'rov — shuning uchun Inertia::location()
     * o'rniga havolani JSON qilib qaytaramiz, frontend uni QR/havola
     * sifatida ko'rsatadi va holatini so'raydi (pollingda).
     */
    public function checkoutOnline(Request $request, string $provider, ClickPaymentService $click, PaymePaymentService $payme)
    {
        abort_unless(in_array($provider, ['click', 'payme'], true), 404);

        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount'      => 'required|numeric|min:1000',
        ], [
            'contract_id.required' => 'Kontraktni tanlang',
            'amount.required'      => 'Summani kiriting',
            'amount.min'           => "Summa 1 000 so'mdan katta bo'lishi kerak",
        ]);

        $contract = Contract::findOrFail($validated['contract_id']);

        if ($contract->status === 'cancelled') {
            return response()->json(['message' => "Shartnoma bekor qilingan"], 422);
        }

        $paidAmount = (float) $contract->payments()->where('status', 'paid')->sum('amount');
        $remaining  = round(max(0, (float) $contract->amount - $paidAmount), 2);

        if ($remaining <= 0) {
            return response()->json(['message' => "Shartnoma bo'yicha qarz mavjud emas"], 422);
        }

        if ($validated['amount'] > $remaining + 0.01) {
            return response()->json([
                'message' => "Kiritilgan summa qolgan qarzdan (".number_format($remaining, 0, '', ' ')." so'm) katta bo'lishi mumkin emas",
            ], 422);
        }

        $payment = Payment::create([
            'contract_id' => $contract->id,
            'user_id'     => Auth::id(),
            'amount'      => $validated['amount'],
            'provider'    => $provider,
            'status'      => 'pending',
        ]);

        // Payer (talaba/abituriyent yoki uning yaqini) to'lovni O'Z
        // qurilmasida (masalan telefonida) yakunlaydi — shuning uchun
        // qaytish manzili login talab qilmaydigan OMMAVIY sahifa
        // (routes/web.php'ga qarang), admin panelining o'ziga emas.
        $returnUrl = route('payments.public.return', $payment->id);

        $checkoutUrl = $provider === 'click'
            ? $click->buildCheckoutUrl($payment, $returnUrl)
            : $payme->buildCheckoutUrl($payment, $returnUrl);

        return response()->json([
            'payment_id'   => $payment->id,
            'checkout_url' => $checkoutUrl,
        ]);
    }

    // Admin panelidagi modal shu manzilni bir necha soniyada bir marta
    // so'rab, to'lov Click/Payme callback'i orqali tasdiqlanganini biladi.
    public function status(Payment $payment)
    {
        return response()->json(['status' => $payment->status]);
    }
}
