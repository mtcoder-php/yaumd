<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookReservation;
use App\Services\LibraryReservationService;
use Illuminate\Http\Request;

/**
 * Kutubxonachi tomonidan navbat (band qilish) ustidan boshqaruv —
 * LibraryLoanController'dan ATAYLAB alohida (u kitob BERISH/QAYTARISH
 * jarayoni, bu esa navbatning o'zini boshqarish: masalan shaxs
 * qo'ng'iroqqa javob bermasa/kelmasa, kutubxonachi navbatni qo'lda ham
 * bekor qila olishi kerak).
 */
class LibraryReservationController extends Controller
{
    public function cancel(Request $request, int $id, LibraryReservationService $reservations)
    {
        $reservation = BookReservation::findOrFail($id);

        $reservations->cancel($reservation, $request->user()->id, 'librarian');

        return back()->with('success', "Band qilish bekor qilindi.");
    }
}
