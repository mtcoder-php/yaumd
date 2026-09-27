<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\LibraryBook;
use App\Models\LibraryLoanNotification;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Kutubxona abonementi — kitob berish/qaytarish. `LibraryBookController`/
 * `BookCopyController`dan ATAYLAB alohida, chunki ular bibliografik
 * yozuv/inventar CRUD'i, bu esa "kimga berildi" jarayonining o'zi.
 */
class LibraryLoanController extends Controller
{
    /**
     * Kitob berish oynasida talaba/xodim qidirish — PersonMatchController::
     * searchCandidates() bilan bir xil naqsh (ataylab qayta yozilgan, chunki
     * bu yerdagi ruxsat ('library.create') turniket moslashtirish ruxsatidan
     * ('turnstile.match') mustaqil bo'lishi kerak).
     */
    public function searchBorrowers(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:student,staff',
            'q' => 'nullable|string|max:100',
        ]);

        $search = trim($data['q'] ?? '');

        if ($search === '') {
            return response()->json([]);
        }

        if ($data['type'] === 'student') {
            $results = Student::query()
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                })
                ->limit(15)
                ->get()
                ->map(fn (Student $s) => [
                    'type' => 'student',
                    'id' => $s->id,
                    'name' => trim("{$s->last_name} {$s->first_name} {$s->middle_name}"),
                    'extra' => $s->student_number,
                ]);
        } else {
            $results = User::query()
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))
                ->where('full_name', 'like', "%{$search}%")
                ->limit(15)
                ->get()
                ->map(fn (User $u) => [
                    'type' => 'staff',
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'extra' => $u->email,
                ]);
        }

        return response()->json($results->values());
    }

    /**
     * Bo'sh nusxani talaba/xodimga berish. Standart muddat (kun) 'Setting'
     * orqali sozlanadi — NotifyStaffAttendance'dagi ish vaqti bilan bir xil
     * konventsiya (admin panelda hali maxsus sozlama sahifasi yo'q, lekin
     * qiymat allaqachon shu kalitdan o'qiladi, kerak bo'lganda keyinroq UI
     * qo'shish qo'shimcha migratsiyasiz bo'ladi).
     */
    public function store(Request $request, int $bookId, TelegramService $telegram)
    {
        $book = LibraryBook::findOrFail($bookId);

        $defaultDays = (int) Setting::get('library.loan_days_default', 14);

        $data = $request->validate([
            'book_copy_id' => 'required|integer|exists:book_copies,id',
            'borrower_type' => 'required|in:student,staff',
            'borrower_id' => 'required|integer',
            'due_date' => 'nullable|date|after:today',
        ]);

        $copy = BookCopy::where('book_id', $book->id)->findOrFail($data['book_copy_id']);

        if ($copy->status !== 'available') {
            return back()->with('error', "Bu nusxa hozir bo'sh emas.");
        }

        $borrowerModel = $data['borrower_type'] === 'student'
            ? Student::find($data['borrower_id'])
            : User::find($data['borrower_id']);

        if (! $borrowerModel) {
            return back()->with('error', "Tanlangan shaxs topilmadi.");
        }

        $dueDate = $data['due_date']
            ? Carbon::parse($data['due_date'])
            : Carbon::today()->addDays($defaultDays);

        $loan = BookLoan::create([
            'book_copy_id' => $copy->id,
            'borrower_type' => $data['borrower_type'],
            'borrower_id' => $borrowerModel->id,
            'issued_by' => $request->user()->id,
            'borrowed_at' => now(),
            'due_date' => $dueDate,
            'status' => BookLoan::STATUS_ACTIVE,
        ]);

        $copy->update(['status' => 'loaned']);

        // Kitob berilgan zahoti darhol tasdiq xabari — 'library:notify-loans'
        // buyrug'i orqali keladigan (muddat yaqinlashganda/o'tganda) eslatma
        // xabarlaridan MUSTAQIL, alohida bir martalik xabar. Borrower
        // Telegram botga ulanmagan bo'lsa (chat_id yo'q) — jim o'tkazib
        // yuboriladi, so'rov muvaffaqiyatsiz bo'lmaydi.
        if ($borrowerModel->telegram_chat_id) {
            $telegram->sendMessage(
                $borrowerModel->telegram_chat_id,
                "📚 Sizga <b>{$book->title}</b> kitobi berildi.\nQaytarish muddati: <b>{$dueDate->format('d.m.Y')}</b>."
            );

            LibraryLoanNotification::create([
                'book_loan_id' => $loan->id,
                'type' => LibraryLoanNotification::TYPE_ISSUED,
                'week' => 0,
                'sent_at' => now(),
            ]);
        }

        return back()->with('success', "Kitob berildi! Qaytarish muddati: {$dueDate->format('d.m.Y')}.");
    }

    /**
     * Kitob qaytarib olinganda — nusxa yana "mavjud" bo'lib qoladi.
     */
    public function returnLoan(Request $request, int $loanId)
    {
        $loan = BookLoan::with('bookCopy')->findOrFail($loanId);

        if ($loan->status !== BookLoan::STATUS_ACTIVE) {
            return back()->with('error', 'Bu kitob allaqachon qaytarilgan/yopilgan.');
        }

        $loan->update([
            'status' => BookLoan::STATUS_RETURNED,
            'returned_at' => now(),
            'returned_to' => $request->user()->id,
        ]);

        $loan->bookCopy->update(['status' => 'available']);

        return back()->with('success', "Kitob qaytarib olindi!");
    }
}
