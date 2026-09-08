<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;

/**
 * Moliya xodimi kassada Click/Payme havolasini/QR'ini generatsiya qilganda
 * (PaymentController::checkoutOnline), to'lovchi (talaba/abituriyent yoki
 * uning yaqini) o'z qurilmasida to'laydi va shu manzilga qaytariladi.
 * To'lovchi bizning tizimimizda umuman login qilmagan bo'lishi mumkin
 * (masalan otasi o'z telefonidan to'lasa), shuning uchun bu sahifa ATAYLAB
 * login talab qilmaydi va admin panelidan butunlay mustaqil, oddiy Blade
 * ko'rinishida.
 *
 * MUHIM: bu yerdagi holat faqat KO'RSATISH uchun — haqiqiy tasdiq
 * (Contract holatini yangilash) bu yerda emas, balki server-serverga
 * keladigan Click/Payme callback'ida (ClickCallbackController /
 * PaymeCallbackController) allaqachon amalga oshirilgan yoki oshirilishi
 * kutilmoqda bo'ladi.
 */
class PublicPaymentReturnController extends Controller
{
    public function contract(int $payment)
    {
        $payment = Payment::with('contract')->findOrFail($payment);

        return view('payment-return', [
            'status'         => $payment->status,
            'amount'         => (float) $payment->amount,
            'contractNumber' => $payment->contract?->contract_number,
        ]);
    }
}
