<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\PaymentCheckoutService;
use Illuminate\Http\Request;

// Talaba pullik kursni Click yoki Payme orqali sotib olishi uchun.
// BookPurchaseController bilan bir xil naqsh — haqiqiy checkout mantig'i
// PaymentCheckoutService'da.
class CoursePurchaseController extends Controller
{
    public function __construct(private PaymentCheckoutService $checkout) {}

    public function checkout(Request $request, int $id, string $provider)
    {
        $course = Course::where('status', 'published')->findOrFail($id);

        return $this->checkout->checkout($course, auth()->id(), $provider);
    }
}
