<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Student;
use App\Services\ClickPaymentService;
use App\Services\PaymePaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Talaba o'zining shartnoma qarzini (to'liq yoki qisman) Click yoki Payme
 * orqali onlayn to'lashi uchun. BookPurchaseController/CoursePurchaseController
 * bilan bir xil naqsh (checkout -> Inertia::location() -> gateway ->
 * return), lekin ular umumiy 'payment_orders' jadvaliga yozadigan
 * PaymentCheckoutService'dan foydalanadi — shartnoma to'lovlari esa
 * ATAYLAB mavjud 'payments' jadvaliga to'g'ridan-to'g'ri yoziladi, shunda
 * moliya bo'limining '/admin/payments' ro'yxati va statistikasi onlayn
 * to'lovlarni ham avtomatik ko'rsatadi (bir xil jadval, bitta hisobot).
 */
class ContractPaymentController extends Controller
{
    public function checkout(Request $request, string $provider, ClickPaymentService $click, PaymePaymentService $payme)
    {
        abort_unless(in_array($provider, ['click', 'payme'], true), 404);

        $student = Student::where('user_id', $request->user()->id)->first();
        $contract = $student ? Contract::where('student_id', $student->id)->first() : null;

        abort_if(! $contract, 404, 'Shartnoma topilmadi');
        abort_if($contract->status === 'cancelled', 404, 'Shartnoma bekor qilingan');

        $paidAmount = (float) $contract->payments()->where('status', 'paid')->sum('amount');
        $remaining = round(max(0, (float) $contract->amount - $paidAmount), 2);

        abort_if($remaining <= 0, 404, "Shartnoma bo'yicha qarz mavjud emas");

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        // Talaba qolgan qarzdan ko'proq summa kiritishi mumkin emas —
        // qisman (masalan oylik) yoki to'liq to'lashni o'zi tanlaydi.
        if ($validated['amount'] > $remaining + 0.01) {
            return back()->withErrors([
                'amount' => "Kiritilgan summa qolgan qarzdan (".number_format($remaining, 0, '', ' ')." so'm) katta bo'lishi mumkin emas",
            ]);
        }

        $payment = Payment::create([
            'contract_id' => $contract->id,
            'user_id'     => $request->user()->id,
            'amount'      => $validated['amount'],
            'provider'    => $provider,
            'status'      => 'pending',
        ]);

        $returnUrl = route('admin.my-contract.payment.return', $payment->id);

        $checkoutUrl = $provider === 'click'
            ? $click->buildCheckoutUrl($payment, $returnUrl)
            : $payme->buildCheckoutUrl($payment, $returnUrl);

        // Bu Inertia so'rovi (Vue'dan router.post orqali chaqiriladi),
        // shuning uchun Inertia::location() ishlatiladi (tashqi to'lov
        // sahifasiga to'liq brauzer navigatsiyasi uchun).
        return Inertia::location($checkoutUrl);
    }

    // Talaba to'lov tizimidan brauzerda shu manzilga qaytariladi. E'TIBOR:
    // haqiqiy tasdiq (Contract holatini yangilash) BU YERDA emas, balki
    // server-serverga keladigan Click/Payme callback'ida
    // (ClickCallbackController / PaymeCallbackController) amalga oshiriladi.
    public function returnFromGateway(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        $message = match ($payment->status) {
            'paid'      => "To'lov muvaffaqiyatli qabul qilindi!",
            'cancelled' => "To'lov bekor qilindi",
            'failed'    => "To'lov amalga oshmadi",
            default     => "To'lov holati tekshirilmoqda — bir necha soniyadan so'ng sahifani yangilang",
        };

        $flashType = in_array($payment->status, ['paid', 'pending'], true) ? 'success' : 'error';

        return redirect()->route('admin.my-contract.show')->with($flashType, $message);
    }
}
