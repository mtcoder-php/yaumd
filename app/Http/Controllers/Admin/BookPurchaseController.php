<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookPurchase;
use App\Models\LibraryBook;
use App\Services\ClickPaymentService;
use App\Services\PaymePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

// Talaba (yoki boshqa har qanday login qilgan foydalanuvchi) pullik
// elektron kitobni Click yoki Payme orqali sotib olishi va, ruxsati bo'lsa,
// faylni yuklab olishi uchun. "Kurslarim"/"Kutubxonam" bilan bir xil
// naqsh: mustaqil controller, permission talab qilinmaydi — faqat auth.
class BookPurchaseController extends Controller
{
    public function checkout(Request $request, int $id, string $provider, ClickPaymentService $click, PaymePaymentService $payme)
    {
        abort_unless(in_array($provider, ['click', 'payme'], true), 404);

        $book = LibraryBook::where('is_active', true)->findOrFail($id);

        if ($book->access_type !== 'paid' || (float) $book->price <= 0) {
            return back()->with('error', "Bu kitob pullik emas");
        }

        if ($book->hasDigitalAccessFor(auth()->id())) {
            return back()->with('success', "Bu kitobni allaqachon sotib olgansiz — yuklab olishingiz mumkin");
        }

        $purchase = BookPurchase::create([
            'book_id'  => $book->id,
            'user_id'  => auth()->id(),
            'amount'   => $book->price,
            'provider' => $provider,
            'status'   => 'pending',
        ]);

        $returnUrl = route('admin.my-library.purchase.return', $purchase->id);

        $checkoutUrl = $provider === 'click'
            ? $click->buildCheckoutUrl($purchase, $returnUrl)
            : $payme->buildCheckoutUrl($purchase, $returnUrl);

        // Bu Inertia so'rovi (Vue'dan router.post orqali chaqiriladi),
        // shuning uchun oddiy redirect()->away() ishlamaydi — Inertia buni
        // "Inertia javobi emas" deb qabul qilmaydi. Inertia::location()
        // maxsus shu holat (tashqi saytga, masalan to'lov tizimiga
        // yo'naltirish) uchun mo'ljallangan: u to'liq brauzer navigatsiyasi
        // (window.location) qiladi.
        return Inertia::location($checkoutUrl);
    }

    // Talaba to'lov tizimidan brauzerda shu manzilga qaytariladi. E'TIBOR:
    // haqiqiy ruxsat (LibraryAccess) BU YERDA emas, balki server-serverga
    // keladigan Click/Payme callback'ida (ClickCallbackController /
    // PaymeCallbackController) beriladi — brauzer orqali qaytish hech
    // qachon ishonchli tasdiq hisoblanmaydi.
    public function return(int $purchaseId)
    {
        $purchase = BookPurchase::findOrFail($purchaseId);

        $message = match ($purchase->status) {
            'paid'      => "To'lov muvaffaqiyatli qabul qilindi! Kitobni endi yuklab olishingiz mumkin.",
            'cancelled' => "To'lov bekor qilindi",
            'failed'    => "To'lov amalga oshmadi",
            default     => "To'lov holati tekshirilmoqda — bir necha soniyadan so'ng sahifani yangilang",
        };

        $flashType = $purchase->status === 'paid' ? 'success' : 'error';
        if ($purchase->status === 'pending') {
            $flashType = 'success';
        }

        return redirect()->route('admin.my-library.show', $purchase->book_id)->with($flashType, $message);
    }

    public function download(int $id)
    {
        $book = LibraryBook::where('is_active', true)->findOrFail($id);

        abort_if(! $book->file_path, 404, "Bu kitobning elektron fayli mavjud emas");
        abort_unless($book->hasDigitalAccessFor(auth()->id()), 403, "Bu kitobni yuklab olish uchun avval sotib olishingiz kerak");

        $book->increment('download_count');

        $extension = pathinfo($book->file_path, PATHINFO_EXTENSION);
        $downloadName = \Illuminate\Support\Str::slug($book->title).'.'.$extension;

        return Storage::disk('local')->download($book->file_path, $downloadName);
    }
}
