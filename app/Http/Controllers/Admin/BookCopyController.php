<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookCopyRequest;
use App\Http\Requests\UpdateBookCopyRequest;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\LibraryBook;
use App\Services\LibraryReservationService;

class BookCopyController extends Controller
{
    public function store(StoreBookCopyRequest $request, int $id, LibraryReservationService $reservations)
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

        // MUHIM: agar yangi nusxalar "mavjud" (available) holatida
        // qo'shilayotgan bo'lsa VA shu kitobga allaqachon navbat (band
        // qilish) bo'lsa — ular DARHOL navbatdagi eng eski
        // kutayotganlarga taqsimlanadi (har biriga Telegram xabari bilan).
        // Bu bo'lmasa, ilgari band qilib navbatga turgan talaba/xodim
        // kitob allaqachon paydo bo'lganidan umuman xabardor bo'lmay,
        // navbati abadiy "kutmoqda" bo'lib qolar edi.
        $assignedToQueue = 0;

        if ($data['status'] === 'available') {
            $newCopies = BookCopy::whereIn('inventory_code', $codes)->get();
            $assignedToQueue = $reservations->assignNewCopiesToQueue($newCopies);
        }

        $range = $quantity > 1 ? "{$codes[0]} – {$codes[array_key_last($codes)]}" : $codes[0];

        $message = "{$quantity} ta nusxa qo'shildi! Inventar raqamlari: {$range}.";
        if ($assignedToQueue > 0) {
            $message .= " Shundan {$assignedToQueue} tasi navbatda kutgan talaba/xodimlarga avtomatik band qilindi va ularga Telegram xabari yuborildi.";
        }

        return back()->with('success', $message);
    }

    public function update(UpdateBookCopyRequest $request, int $id, int $copyId, LibraryReservationService $reservations)
    {
        $copy = BookCopy::where('book_id', $id)->with('activeReservation')->findOrFail($copyId);
        $data = $request->validated();

        // Nusxani "talaba/xodim qo'lida" holatiga FAQAT kitob berish
        // (LibraryLoanController::store) orqali o'tkazish mumkin — bu
        // yerdan qo'lda o'rnatilsa, BookLoan yozuvsiz "loaned" nusxa
        // paydo bo'lib, kim va qachongacha olgani noma'lum qolib ketardi.
        if (($data['status'] ?? $copy->status) === 'loaned' && $copy->status !== 'loaned') {
            return back()->with('error', "Nusxani \"olingan\" holatiga faqat kitob berish orqali o'tkazish mumkin.");
        }

        // Xuddi shu sabab bilan — "band qilingan" holatiga FAQAT
        // rezervatsiya navbati (LibraryReservationService) o'tkazishi
        // mumkin, aks holda BookReservation yozuvsiz "reserved" nusxa
        // paydo bo'lib qolardi (kim uchun ekani noma'lum).
        if (($data['status'] ?? $copy->status) === 'reserved' && $copy->status !== 'reserved') {
            return back()->with('error', "Nusxani \"band qilingan\" holatiga faqat rezervatsiya navbati orqali o'tkazish mumkin.");
        }

        $wasLoaned = $copy->status === 'loaned';
        $wasReserved = $copy->status === 'reserved';
        $activeReservation = $copy->activeReservation;

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

        // Xuddi shunday — nusxa band qilingan edi, lekin admin uni qo'lda
        // boshqa holatga o'tkazsa, eski band qilish yozuvi "ready" bo'lib
        // osilib qolmasin (endi hech qaysi nusxaga ishora qilmaydigan
        // "yolg'on" band bo'lib qolar edi). 'available'ga o'tkazilsa,
        // navbatda boshqa kutayotgan bo'lsa, nusxa DARHOL ularga o'tadi
        // (releaseCopy=true); 'damaged'/'lost' bo'lsa, nusxa endi hech
        // kimga berib bo'lmaydi, shuning uchun band shunchaki bekor
        // qilinadi (releaseCopy=false).
        if ($wasReserved && $copy->status !== 'reserved' && $activeReservation) {
            $reservations->cancel(
                $activeReservation,
                $request->user()->id,
                'librarian_override',
                releaseCopy: $copy->status === 'available',
            );
        }

        return back()->with('success', 'Nusxa ma\'lumotlari yangilandi!');
    }

    public function destroy(int $id, int $copyId)
    {
        $copy = BookCopy::where('book_id', $id)->findOrFail($copyId);

        if ($copy->status === 'loaned') {
            return back()->with('error', "Bu nusxa hozir talaba qo'lida — avval qaytarilishi kerak.");
        }

        if ($copy->status === 'reserved') {
            return back()->with('error', "Bu nusxa hozir band qilingan — avval navbatni bekor qiling yoki shaxs kelib olguncha kuting.");
        }

        $copy->delete();

        return back()->with('success', "Nusxa o'chirildi!");
    }
}
