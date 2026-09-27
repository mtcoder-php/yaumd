<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\LibraryBook;
use App\Models\LibraryLoanNotification;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
     *
     * Ko'p sonli talaba/xodim orasidan tezroq topish uchun matnli qidiruvga
     * qo'shimcha filtrlar ham qo'llab-quvvatlanadi: talaba uchun yo'nalish/
     * kurs/guruh, xodim uchun rol. Natijada rasmi (agar bor bo'lsa) va
     * guruh/lavozim ma'lumoti ham qaytariladi — kutubxonachi bir xil ismli
     * shaxslarni chalkashtirmasligi uchun.
     */
    public function searchBorrowers(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:student,staff',
            'q' => 'nullable|string|max:100',
            'direction_id' => 'nullable|integer',
            'course_year' => 'nullable|integer|min:1|max:6',
            'group_id' => 'nullable|integer',
            'role' => 'nullable|string|max:100',
        ]);

        $search = trim($data['q'] ?? '');
        $hasFilters = $data['type'] === 'student'
            ? (! empty($data['direction_id']) || ! empty($data['course_year']) || ! empty($data['group_id']))
            : ! empty($data['role']);

        // Matn ham, filtr ham berilmagan bo'lsa hech narsa qaytarmaymiz —
        // aks holda bo'sh so'rov butun talabalar/xodimlar jadvalini qaytarib
        // yuborishi mumkin edi.
        if ($search === '' && ! $hasFilters) {
            return response()->json([]);
        }

        if ($data['type'] === 'student') {
            $results = Student::query()
                ->with('direction:id,name_uz')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    });
                })
                ->when(! empty($data['direction_id']), fn ($q) => $q->where('direction_id', $data['direction_id']))
                ->when(! empty($data['course_year']), fn ($q) => $q->where('course_year', $data['course_year']))
                ->when(! empty($data['group_id']), fn ($q) => $q->whereHas(
                    'groups',
                    fn ($q2) => $q2->where('student_groups.id', $data['group_id'])
                ))
                ->limit(15)
                ->get()
                ->map(fn (Student $s) => [
                    'type' => 'student',
                    'id' => $s->id,
                    'name' => trim("{$s->last_name} {$s->first_name} {$s->middle_name}"),
                    'extra' => $s->student_number,
                    'meta' => trim(($s->direction?->name_uz ?? '') . ($s->course_year ? " · {$s->course_year}-kurs" : '')),
                    'photo_url' => $s->photo ? Storage::disk('public')->url($s->photo) : null,
                    'is_active' => $s->status === 'active',
                ]);
        } else {
            $results = User::query()
                ->with('roles:id,name')
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))
                ->when($search !== '', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                ->when(! empty($data['role']), fn ($q) => $q->whereHas('roles', fn ($q2) => $q2->where('name', $data['role'])))
                ->limit(15)
                ->get()
                ->map(fn (User $u) => [
                    'type' => 'staff',
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'extra' => $u->email,
                    'meta' => $u->roles->pluck('name')->implode(', '),
                    'photo_url' => $u->photo_url,
                    'is_active' => (bool) $u->is_active,
                ]);
        }

        return response()->json($results->values());
    }

    /**
     * Talaba filtrida "Guruh" tanlovi — yo'nalish/kurs tanlangach shu
     * ikkoviga mos guruhlar ro'yxatini qaytaradi (kaskadli dropdown).
     */
    public function borrowerGroups(Request $request): JsonResponse
    {
        $data = $request->validate([
            'direction_id' => 'nullable|integer',
            'course_year' => 'nullable|integer|min:1|max:6',
        ]);

        $groups = StudentGroup::query()
            ->where('is_active', true)
            ->when(! empty($data['direction_id']), fn ($q) => $q->where('direction_id', $data['direction_id']))
            ->when(! empty($data['course_year']), fn ($q) => $q->where('course_year', $data['course_year']))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($groups);
    }

    /**
     * Tanlangan talaba/xodimning kutubxona tarixi — kitob berish oynasida
     * shaxs tanlangach ko'rsatiladi: jami necha kitob olgani, hozir nechtasi
     * qo'lida (va muddati o'tganmi) — kutubxonachi yangi kitob berishdan
     * oldin shu ma'lumotni ko'rib qaror qabul qiladi.
     */
    public function borrowerHistory(string $type, int $id): JsonResponse
    {
        abort_unless(in_array($type, ['student', 'staff'], true), 404);

        $loans = BookLoan::where('borrower_type', $type)
            ->where('borrower_id', $id)
            ->with('bookCopy.book:id,title')
            ->latest('borrowed_at')
            ->get();

        $active = $loans->where('status', BookLoan::STATUS_ACTIVE)->values();

        return response()->json([
            'total_count' => $loans->count(),
            'active_count' => $active->count(),
            'overdue_count' => $active->filter(fn (BookLoan $l) => $l->isOverdue())->count(),
            'active_loans' => $active->map(fn (BookLoan $l) => [
                'id' => $l->id,
                'title' => $l->bookCopy?->book?->title ?? '—',
                'due_date' => $l->due_date,
                'is_overdue' => $l->isOverdue(),
            ])->values(),
        ]);
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

        // Bir shaxsda bir vaqtning o'zida qancha kitob bo'lishi mumkinligi
        // chegarasi — kutubxonachi bitta odamga cheksiz kitob berib
        // yubormasligi uchun. 'Setting' orqali sozlanadi (hozircha UI'siz,
        // kerak bo'lsa keyinroq admin sozlamalar sahifasiga qo'shiladi).
        $maxActiveLoans = (int) Setting::get('library.max_active_loans_per_borrower', 3);
        $activeLoansCount = BookLoan::where('borrower_type', $data['borrower_type'])
            ->where('borrower_id', $borrowerModel->id)
            ->where('status', BookLoan::STATUS_ACTIVE)
            ->count();

        if ($activeLoansCount >= $maxActiveLoans) {
            return back()->with('error', "Bu shaxsda hozir {$activeLoansCount} ta faol kitob bor (ruxsat etilgan chegara: {$maxActiveLoans} ta) — yangi kitob berishdan oldin avval mavjudlarini qaytarishi kerak.");
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
