<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentCheckoutService;

// Click/Payme sahifasidan to'lovdan keyin talaba brauzerda qaytadigan
// UMUMIY manzil — mahsulot kitobmi, kursmi, farqi yo'q
// (PaymentCheckoutService buni payment_orders.payable_type orqali o'zi
// aniqlaydi va tegishli sahifaga yo'naltiradi).
class PaymentReturnController extends Controller
{
    public function __invoke(int $orderId, PaymentCheckoutService $checkout)
    {
        return $checkout->handleReturn($orderId);
    }
}
