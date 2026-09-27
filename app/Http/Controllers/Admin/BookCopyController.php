<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookCopyRequest;
use App\Http\Requests\UpdateBookCopyRequest;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\LibraryBook;

class BookCopyController extends Controller
{
    public function store(StoreBookCopyRequest $request, int $id)
    {
        $book = LibraryBook::findOrFail($id);
        $data = $request->validated();
        $quantity = (int) $data['quantity'];

        $codes = BookCopy::nextInventoryCodes($quantity);
        $now = now();

        // Bulk 'insert()' ataylab ishlatiladi (bittalab 'create()' o'rniga)
        // — bir martada 100 tagacha nusxa yaratilishi mumkin bo'lgani
        // uchun, har biriga alohida so'rov yubormaslik uchun. BookCopy'da
        // hech qanday model hodisasi (observer/event) yo'q, shuning uchun
        // bulk insert'ning ularni chetlab o'tishi bu yerda muammo emas.
        $rows = array_map(fn (string $code) => [
            'book_id'         => $book->id,
            'inventory_code'  => $code,
            'status'          => $data['status'],
            'condition_notes' => $data['condition_notes'] ?? null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ], $codes);

        BookCopy::insert($rows);

        $range = $quantity > 1 ? "{$codes[0]} – {$codes[array_key_last($codes)]}" : $codes[0];

        return back()->with('success', "{$quantity} ta nusxa qo'shildi! Inventar raqamlari: {$range}.");
    }

    public function update(UpdateBookCopyRequest $request, int $id, int $copyId)
    {
        $copy = BookCopy::where('book_id', $id)->findOrFail($copyId);
        $data = $request->validated();

        // Nusxani "talaba/xodim qo'lida" holatiga FAQAT kitob berish
        // (LibraryLoanController::store) orqali o'tkazish mumkin — bu
        // yerdan qo'lda o'rnatilsa, BookLoan yozuvsiz "loaned" nusxa
        // paydo bo'lib, kim va qachongacha olgani noma'lum qolib ketardi.
        if (($data['status'] ?? $copy->status) === 'loaned' && $copy->status !== 'loaned') {
            return back()->with('error', "Nusxani \"olingan\" holatiga faqat kitob berish orqali o'tkazish mumkin.");
        }

        $wasLoaned = $copy->status === 'loaned';

        $copy->update($data);

        // Admin nusxani "olingan"dan boshqa holatga (masalan yo'qolgan/
        // shikastlangan) qo'lda o'zgartirsa, tegishli FAOL abonement
        // yozuvi ham yopiladi — aks holda u "active" bo'lib qolib,
        // NotifyLibraryLoans abadiy eslatma yuborishda davom etardi.
        if ($wasLoaned && $copy->status !== 'loaned') {
            $activeLoan = $copy->loans()->where('status', BookLoan::STATUS_ACTIVE)->latest()->first();

            if ($activeLoan) {
                $activeLoan->update([
                    'status' => $copy->status === 'lost' ? BookLoan::STATUS_LOST : BookLoan::STATUS_RETURNED,
                    'returned_at' => now(),
                    'returned_to' => $request->user()->id,
                ]);
            }
        }

        return back()->with('success', 'Nusxa ma\'lumotlari yangilandi!');
    }

    public function destroy(int $id, int $copyId)
    {
        $copy = BookCopy::where('book_id', $id)->findOrFail($copyId);

        if ($copy->status === 'loaned') {
            return back()->with('error', "Bu nusxa hozir talaba qo'lida — avval qaytarilishi kerak.");
        }

        $copy->delete();

        return back()->with('success', "Nusxa o'chirildi!");
    }
}
