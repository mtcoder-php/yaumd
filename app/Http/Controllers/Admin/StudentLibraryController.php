<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookReservation;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\Student;
use App\Services\LibraryReservationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Talabalar (va boshqa har qanday login qilgan foydalanuvchi) uchun
// kutubxona katalogini FAQAT KO'RISH sahifasi. "Kurslarim"dagi
// StudentCourseController bilan bir xil naqsh: alohida, mustaqil
// controller — routes/admin.php'da 'permission:' talab qilinmaydi, faqat
// 'auth' yetarli, chunki bu yerda hech qanday CRUD yo'q, faqat faol
// kitoblarni ko'rish (+ endi band qilish/bekor qilish).
class StudentLibraryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = LibraryBook::with('category')
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->orderBy('title');

        [$borrowerType, $borrowerId] = $this->currentBorrower($request);

        return Inertia::render('Student/Library/Index', [
            'books'      => $query->paginate(24)->withQueryString(),
            'categories' => LibraryCategory::where('is_active', true)->orderBy('name_uz')->get(['id', 'name_uz']),
            'filters'    => $request->only(['category_id', 'search']),
            // "Mening navbatlarim" paneli uchun — shu shaxsning barcha
            // FAOL (hali kelib olmagan) band qilishlari, qaysi kitobga
            // taalluqli ekani bilan birga.
            'myReservations' => BookReservation::where('borrower_type', $borrowerType)
                ->where('borrower_id', $borrowerId)
                ->whereIn('status', [BookReservation::STATUS_WAITING, BookReservation::STATUS_READY])
                ->with('book:id,title,cover_image')
                ->latest('created_at')
                ->get()
                ->map(fn (BookReservation $r) => $this->formatReservation($r)),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $book = LibraryBook::with('category')
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->where('is_active', true)
            ->findOrFail($id);

        [$borrowerType, $borrowerId] = $this->currentBorrower($request);

        $reservation = app(LibraryReservationService::class)->findActiveForBorrower($book, $borrowerType, $borrowerId);

        return Inertia::render('Student/Library/Show', [
            'book'            => $book,
            // Talaba pullik kitobni allaqachon sotib olganmi (yoki kitob
            // bepul bo'lsa — har doim true). Frontend shu bittasiga qarab
            // "Sotib olish" yoki "Yuklab olish" tugmasini ko'rsatadi.
            'hasDigitalAccess' => $book->hasAccessFor(auth()->id()),
            // Shu shaxsning AYNAN shu kitobdagi joriy band qilishi (bor
            // bo'lsa) — frontend shu orqali "Band qilish" tugmasi o'rniga
            // navbatdagi holatni (yoki "kelib oling" xabarini) ko'rsatadi.
            'myReservation' => $reservation ? $this->formatReservation($reservation) : null,
        ]);
    }

    /**
     * Barcha nusxalari band bo'lgan kitobga navbatga turish.
     */
    public function reserve(Request $request, int $id)
    {
        $book = LibraryBook::where('is_active', true)->findOrFail($id);
        [$borrowerType, $borrowerId] = $this->currentBorrower($request);

        try {
            app(LibraryReservationService::class)->reserve($book, $borrowerType, $borrowerId);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Navbatga qo'shildingiz! Kitob bo'shashi bilan Telegram orqali xabar beramiz.");
    }

    /**
     * Talaba/xodim o'zining band qilishini ("mening navbatlarim" ro'yxati
     * yoki kitob sahifasidan) bekor qiladi.
     */
    public function cancelReservation(Request $request, int $id)
    {
        [$borrowerType, $borrowerId] = $this->currentBorrower($request);

        $reservation = BookReservation::where('borrower_type', $borrowerType)
            ->where('borrower_id', $borrowerId)
            ->findOrFail($id);

        app(LibraryReservationService::class)->cancel($reservation, null, 'user');

        return back()->with('success', 'Band qilish bekor qilindi.');
    }

    /**
     * Joriy foydalanuvchining kutubxona tizimidagi "shaxsi" — agar Student
     * yozuviga bog'langan bo'lsa talaba, aks holda xodimning o'zi (User).
     * LibraryLoanController'dagi bilan bir xil polimorfik naqsh
     * ('student' => Student, 'staff' => User).
     *
     * @return array{0: string, 1: int}
     */
    private function currentBorrower(Request $request): array
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        if ($student) {
            return ['student', $student->id];
        }

        return ['staff', $request->user()->id];
    }

    private function formatReservation(BookReservation $reservation): array
    {
        return [
            'id'             => $reservation->id,
            'status'         => $reservation->status,
            'book'           => $reservation->relationLoaded('book') && $reservation->book ? [
                'id'              => $reservation->book->id,
                'title'           => $reservation->book->title,
                'cover_image_url' => $reservation->book->cover_image_url,
            ] : null,
            'queue_position' => $reservation->status === BookReservation::STATUS_WAITING
                ? app(LibraryReservationService::class)->queuePosition($reservation)
                : null,
            'expires_at'     => $reservation->expires_at,
            'created_at'     => $reservation->created_at,
        ];
    }
}
