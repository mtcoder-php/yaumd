<?php

namespace App\Services;

use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\BookReservation;
use App\Models\LibraryBook;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Kitob band qilish (rezervatsiya) navbatining barcha ishbiznes qoidalari
 * shu yerda jamlangan — controller'lar (student va admin tomon) faqat shu
 * xizmatni chaqiradi, takrorlanish bo'lmasligi uchun.
 *
 * Asosiy qoida: band qilish FAQAT kitobning barcha nusxalari band bo'lganda
 * ruxsat etiladi (agar bo'sh nusxa bo'lsa, kutubxonachi to'g'ridan-to'g'ri
 * berishi kerak). Nusxa qaytarilganda/bo'shaganda, navbatdagi BIRINCHI
 * 'waiting' yozuv 'ready'ga o'tadi, nusxa 'reserved' bo'lib belgilanadi va
 * shaxsga (agar Telegram ulangan bo'lsa) xabar boradi — belgilangan
 * muddat (Setting: 'library.reservation_pickup_hours') ichida kelib
 * olmasa, ExpireLibraryReservations buyrug'i uni avtomatik 'expired'
 * qilib, navbatni keyingi odamga suradi.
 */
class LibraryReservationService
{
    public function __construct(private TelegramService $telegram)
    {
    }

    public function maxActiveReservationsPerBorrower(): int
    {
        return (int) Setting::get('library.max_active_reservations_per_borrower', 3);
    }

    public function pickupHours(): int
    {
        return (int) Setting::get('library.reservation_pickup_hours', 24);
    }

    /**
     * Talaba/xodim shu kitobga navbatga turadi.
     *
     * @throws \RuntimeException inson o'qiy oladigan sabab bilan — controller
     *                            buni to'g'ridan-to'g'ri flash xabar sifatida
     *                            foydalanuvchiga ko'rsatadi.
     */
    public function reserve(LibraryBook $book, string $borrowerType, int $borrowerId): BookReservation
    {
        return DB::transaction(function () use ($book, $borrowerType, $borrowerId) {
            // MUHIM: qulflab (lockForUpdate) hisoblanadi — aks holda ikki
            // kishi AYNAN bir vaqtda "band qilish"ni bossa, ikkalasi ham
            // "bo'sh nusxa yo'q" tekshiruvidan xavfsiz o'tib, tasodifan
            // ikkalasi ham navbatga yozilib qolishi mumkin edi (bu holatda
            // zarari yo'q, lekin qulflash umuman shu turdagi poyga
            // holatlarining oldini oladi).
            $availableCount = BookCopy::where('book_id', $book->id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->count();

            if ($availableCount > 0) {
                throw new \RuntimeException("Bu kitobning bo'sh nusxasi bor — band qilish shart emas, to'g'ridan-to'g'ri kutubxonadan so'rang.");
            }

            $alreadyQueued = BookReservation::where('book_id', $book->id)
                ->where('borrower_type', $borrowerType)
                ->where('borrower_id', $borrowerId)
                ->whereIn('status', [BookReservation::STATUS_WAITING, BookReservation::STATUS_READY])
                ->exists();

            if ($alreadyQueued) {
                throw new \RuntimeException('Siz bu kitobga allaqachon navbatdasiz.');
            }

            $max = $this->maxActiveReservationsPerBorrower();
            $activeCount = BookReservation::where('borrower_type', $borrowerType)
                ->where('borrower_id', $borrowerId)
                ->whereIn('status', [BookReservation::STATUS_WAITING, BookReservation::STATUS_READY])
                ->count();

            if ($activeCount >= $max) {
                throw new \RuntimeException("Siz bir vaqtning o'zida {$max} tadan ortiq kitobga navbatda tura olmaysiz — avval ba'zilarini bekor qiling.");
            }

            return BookReservation::create([
                'book_id'       => $book->id,
                'borrower_type' => $borrowerType,
                'borrower_id'   => $borrowerId,
                'status'        => BookReservation::STATUS_WAITING,
            ]);
        });
    }

    /**
     * Band qilishni bekor qiladi — talabaning o'zi ("mening navbatlarim"
     * sahifasidan) yoki kutubxonachi (navbat panelidan, yoki reserved
     * nusxani boshqasiga override qilib berganda) chaqirishi mumkin.
     *
     * $releaseCopy=false FAQAT kutubxonachi shu bekor qilingan yozuvning
     * nusxasini DARHOL boshqa shaxsga berayotgan holatda ishlatiladi —
     * aks holda bu metod nusxani navbatdagi KEYINGI odamga avtomatik
     * o'tkazib yuborar edi, keyin esa chaqiruvchi tomon uni yana o'zgacha
     * "loaned" holatiga o'tkazishga urinib, ikkalasi bir-biriga zid kelardi.
     */
    public function cancel(
        BookReservation $reservation,
        ?int $cancelledByUserId = null,
        string $reason = 'user',
        bool $releaseCopy = true,
    ): void {
        if (! in_array($reservation->status, [BookReservation::STATUS_WAITING, BookReservation::STATUS_READY], true)) {
            return;
        }

        $wasReady = $reservation->status === BookReservation::STATUS_READY;
        $copy = $reservation->bookCopy;

        $reservation->update([
            'status'        => BookReservation::STATUS_CANCELLED,
            'cancelled_by'  => $cancelledByUserId,
            'cancel_reason' => $reason,
            'cancelled_at'  => now(),
        ]);

        if ($wasReady && $copy && $releaseCopy) {
            $this->releaseCopy($copy);
        }
    }

    /**
     * Bitta nusxa "band" zanjiridan chiqadi — yo kimdir uni qaytardi, yo
     * band qilgan kishi muddatida kelib olmadi/bekor qildi. Agar shu
     * kitobga hali navbatda odam bo'lsa, nusxa DARHOL ularga o'tkaziladi
     * (yana 'reserved'ga aylanadi), aks holda oddiy 'available' bo'ladi.
     */
    public function releaseCopy(BookCopy $copy): void
    {
        $next = BookReservation::where('book_id', $copy->book_id)
            ->where('status', BookReservation::STATUS_WAITING)
            ->oldest('created_at')
            ->first();

        if (! $next) {
            $copy->update(['status' => 'available']);

            return;
        }

        $this->assignCopyToReservation($next, $copy);
    }

    private function assignCopyToReservation(BookReservation $reservation, BookCopy $copy): void
    {
        $expiresAt = now()->addHours($this->pickupHours());

        $reservation->update([
            'book_copy_id' => $copy->id,
            'status'       => BookReservation::STATUS_READY,
            'notified_at'  => now(),
            'expires_at'   => $expiresAt,
        ]);

        $copy->update(['status' => 'reserved']);

        $borrower = $reservation->borrower;

        if ($borrower?->telegram_chat_id) {
            $title = $reservation->book?->title ?? $copy->book?->title ?? 'Kitob';

            $this->telegram->sendMessage(
                $borrower->telegram_chat_id,
                "📚 Siz navbatda turgan <b>{$title}</b> kitobi bo'shadi!\n"
                . "Uni <b>{$expiresAt->format('d.m.Y H:i')}</b>gacha kutubxonadan kelib olishingiz kerak, "
                . "aks holda band bekor qilinib, navbatdagi keyingi shaxsga o'tadi."
            );
        }
    }

    /**
     * LibraryLoanController::returnLoan() — nusxa 'available' qilib
     * belgilangandan KEYIN chaqiriladi (agar navbat bo'lsa, shu metod uni
     * darhol qayta 'reserved'ga o'tkazadi).
     */
    public function onCopyReturned(BookCopy $copy): void
    {
        $this->releaseCopy($copy);
    }

    /**
     * LibraryLoanController::store() — kitob ANIQ shu band qilgan shaxsga
     * berilganda, band qilish yozuvini "bajarildi"ga o'tkazadi.
     */
    public function fulfil(BookReservation $reservation, BookLoan $loan): void
    {
        $reservation->update([
            'status'             => BookReservation::STATUS_FULFILLED,
            'fulfilled_loan_id'  => $loan->id,
        ]);
    }

    /**
     * ExpireLibraryReservations buyrug'i uchun — muddati o'tib ketgan
     * 'ready' yozuvlarni 'expired' qilib, navbatni keyingi odamga suradi.
     *
     * @return int nechta yozuvning muddati o'tkazilgani
     */
    public function expireStale(): int
    {
        $stale = BookReservation::where('status', BookReservation::STATUS_READY)
            ->where('expires_at', '<', now())
            ->with('bookCopy')
            ->get();

        foreach ($stale as $reservation) {
            $copy = $reservation->bookCopy;

            $reservation->update(['status' => BookReservation::STATUS_EXPIRED]);

            if ($copy) {
                $this->releaseCopy($copy);
            }
        }

        return $stale->count();
    }

    /**
     * Kitobga yangi fizik nusxa(lar) qo'shilganda (BookCopyController::
     * store()) chaqiriladi — agar shu kitobga allaqachon navbat bo'lsa,
     * yangi nusxalar DARHOL navbatdagi eng eski kutayotganlarga
     * taqsimlanadi (har biriga alohida Telegram xabari bilan), qolgani
     * esa oddiy 'available' bo'lib qoladi.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, BookCopy>  $newCopies  Yangi qo'shilgan, hali 'available' bo'lgan nusxalar
     * @return int nechta nusxa navbatga taqsimlangani
     */
    public function assignNewCopiesToQueue(\Illuminate\Database\Eloquent\Collection $newCopies): int
    {
        if ($newCopies->isEmpty()) {
            return 0;
        }

        $waitingCount = BookReservation::where('book_id', $newCopies->first()->book_id)
            ->where('status', BookReservation::STATUS_WAITING)
            ->count();

        if ($waitingCount === 0) {
            return 0;
        }

        $toAssign = min($waitingCount, $newCopies->count());
        $assigned = 0;

        foreach ($newCopies->take($toAssign) as $copy) {
            $this->releaseCopy($copy);
            $assigned++;
        }

        return $assigned;
    }

    /**
     * Kitob (qaysi yo'l bilan bo'lmasin — band qilingan nusxadan yoki
     * oddiy bo'sh nusxadan) shu shaxsga berilgach chaqiriladi: agar shu
     * shaxsning AYNAN shu kitobga boshqa FAOL (waiting/ready) band
     * qilishi ham qolib ketgan bo'lsa (masalan kutubxonachi navbatni
     * hisobga olmay, boshqa bo'sh nusxadan to'g'ridan-to'g'ri bergan
     * bo'lsa), u ham yopiladi — aks holda odam kitobni allaqachon qo'lga
     * olgan bo'lsa ham, navbatda/band bo'lib abadiy osilib qolar edi.
     * Agar o'sha eski band qilish ALOHIDA nusxaga bog'langan bo'lsa
     * (status='ready'), o'sha nusxa ham bo'shatiladi (navbatda yana
     * boshqa kimdir bo'lsa, unga o'tadi).
     */
    public function resolveDanglingReservations(
        LibraryBook $book,
        string $borrowerType,
        int $borrowerId,
        BookLoan $loan,
        ?int $exceptReservationId = null,
    ): void {
        $stale = BookReservation::where('book_id', $book->id)
            ->where('borrower_type', $borrowerType)
            ->where('borrower_id', $borrowerId)
            ->whereIn('status', [BookReservation::STATUS_WAITING, BookReservation::STATUS_READY])
            ->when($exceptReservationId, fn ($q) => $q->where('id', '!=', $exceptReservationId))
            ->with('bookCopy')
            ->get();

        foreach ($stale as $reservation) {
            $oldCopy = $reservation->bookCopy;

            $this->fulfil($reservation, $loan);

            if ($oldCopy && $oldCopy->id !== $loan->book_copy_id) {
                $this->releaseCopy($oldCopy->fresh());
            }
        }
    }

    /**
     * Shu talaba/xodimning shu kitobdagi FAOL (waiting/ready) band qilishi
     * — Student/Library/Show.vue "band qilish" tugmasi o'rniga joriy
     * holatni ko'rsatishi uchun.
     */
    public function findActiveForBorrower(LibraryBook $book, string $borrowerType, int $borrowerId): ?BookReservation
    {
        return BookReservation::where('book_id', $book->id)
            ->where('borrower_type', $borrowerType)
            ->where('borrower_id', $borrowerId)
            ->whereIn('status', [BookReservation::STATUS_WAITING, BookReservation::STATUS_READY])
            ->first();
    }

    /**
     * 'waiting' holatidagi yozuvning navbatdagi o'rni (1-dan boshlab) —
     * o'zidan OLDIN yaratilgan, hali ham 'waiting' bo'lgan yozuvlar soni + 1.
     * Statik saqlanmaydi (BookLoan::isOverdue()dagi bilan bir xil sabab).
     */
    public function queuePosition(BookReservation $reservation): int
    {
        if ($reservation->status !== BookReservation::STATUS_WAITING) {
            return 0;
        }

        return 1 + BookReservation::where('book_id', $reservation->book_id)
                ->where('status', BookReservation::STATUS_WAITING)
                ->where('created_at', '<', $reservation->created_at)
                ->count();
    }
}
