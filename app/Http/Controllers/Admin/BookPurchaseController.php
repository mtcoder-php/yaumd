<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Services\PaymentCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Talaba (yoki boshqa har qanday login qilgan foydalanuvchi) pullik
// elektron kitobni Click yoki Payme orqali sotib olishi va, ruxsati bo'lsa,
// faylni yuklab olishi uchun. Haqiqiy Click/Payme checkout mantig'i
// PaymentCheckoutService'da — bu yerda faqat "qaysi kitob" ekanligini
// aniqlab, shu servisga uzatamiz (CoursePurchaseController xuddi shunday
// ishlaydi, lekin Course uchun).
class BookPurchaseController extends Controller
{
    public function __construct(private PaymentCheckoutService $checkout) {}

    public function checkout(Request $request, int $id, string $provider)
    {
        $book = LibraryBook::where('is_active', true)->findOrFail($id);

        return $this->checkout->checkout($book, auth()->id(), $provider);
    }

    public function download(int $id)
    {
        $book = LibraryBook::where('is_active', true)->findOrFail($id);

        abort_if(! $book->file_path, 404, "Bu kitobning elektron fayli mavjud emas");
        abort_unless($book->hasAccessFor(auth()->id()), 403, "Bu kitobni yuklab olish uchun avval sotib olishingiz kerak");

        $book->increment('download_count');

        $extension = pathinfo($book->file_path, PATHINFO_EXTENSION);
        $downloadName = \Illuminate\Support\Str::slug($book->title).'.'.$extension;

        return Storage::disk('local')->download($book->file_path, $downloadName);
    }
}
