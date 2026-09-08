<?php

namespace App\Services;

use App\Contracts\Purchasable;
use App\Models\Course;
use App\Models\LibraryBook;
use App\Models\PaymentOrder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * Click/Payme orqali "biror narsani" (elektron kitob, pullik kurs...)
 * sotib olishning UMUMIY oqimi. BookPurchaseController va
 * CoursePurchaseController — ikkalasi ham shu servisga murojaat qiladi,
 * shu sababli haqiqiy Click/Payme checkout mantig'i FAQAT SHU YERDA
 * yoziladi (App\Contracts\Purchasable'ga qarang).
 */
class PaymentCheckoutService
{
    public function __construct(
        private ClickPaymentService $click,
        private PaymePaymentService $payme,
    ) {
    }

    public function checkout(Purchasable $payable, int $userId, string $provider)
    {
        abort_unless(in_array($provider, ['click', 'payme'], true), 404);
        abort_unless($payable->canBePurchased(), 404, "Bu mahsulot sotib olish uchun mavjud emas");

        if ($payable->hasAccessFor($userId)) {
            return back()->with('success', "Bu mahsulotni allaqachon sotib olgansiz");
        }

        $order = PaymentOrder::create([
            'payable_type' => $payable->getMorphClass(),
            'payable_id'   => $payable->getKey(),
            'user_id'      => $userId,
            'amount'       => $payable->purchasePrice(),
            'provider'     => $provider,
            'status'       => 'pending',
        ]);

        $returnUrl = route('admin.payments.return', $order->id);

        $checkoutUrl = $provider === 'click'
            ? $this->click->buildCheckoutUrl($order, $returnUrl)
            : $this->payme->buildCheckoutUrl($order, $returnUrl);

        // Bu Inertia so'rovi (Vue'dan router.post orqali chaqiriladi),
        // shuning uchun oddiy redirect()->away() ishlamaydi — Inertia buni
        // "Inertia javobi emas" deb qabul qilmaydi. Inertia::location()
        // maxsus shu holat (tashqi saytga, masalan to'lov tizimiga
        // yo'naltirish) uchun mo'ljallangan: u to'liq brauzer navigatsiyasi
        // (window.location) qiladi.
        return Inertia::location($checkoutUrl);
    }

    // Talaba to'lov tizimidan brauzerda shu manzilga qaytariladi. E'TIBOR:
    // haqiqiy ruxsat (LibraryAccess/Enrollment) BU YERDA emas, balki
    // server-serverga keladigan Click/Payme callback'ida
    // (ClickCallbackController / PaymeCallbackController) beriladi —
    // brauzer orqali qaytish hech qachon ishonchli tasdiq hisoblanmaydi.
    public function handleReturn(int $orderId): RedirectResponse
    {
        $order = PaymentOrder::findOrFail($orderId);

        $message = match ($order->status) {
            'paid'      => "To'lov muvaffaqiyatli qabul qilindi!",
            'cancelled' => "To'lov bekor qilindi",
            'failed'    => "To'lov amalga oshmadi",
            default     => "To'lov holati tekshirilmoqda — bir necha soniyadan so'ng sahifani yangilang",
        };

        $flashType = in_array($order->status, ['paid', 'pending'], true) ? 'success' : 'error';

        return redirect()->to($this->redirectTarget($order))->with($flashType, $message);
    }

    // Har xil turdagi mahsulot uchun to'lovdan keyin qaytariladigan sahifa
    // manzili — yangi turdagi Purchasable qo'shilsa, shu joyga bitta qator
    // qo'shish kifoya.
    private function redirectTarget(PaymentOrder $order): string
    {
        return match ($order->payable_type) {
            LibraryBook::class => route('admin.my-library.show', $order->payable_id),
            Course::class      => route('admin.course-catalog.show', $order->payable_id),
            default            => route('admin.dashboard'),
        };
    }
}
