<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Kitob band qilish (rezervatsiya) navbatidagi bitta yozuv. To'liq
 * tushuntirish uchun migratsiyaga qarang.
 */
class BookReservation extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_READY = 'ready';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    // BookLoan'dagi bilan bir xil sabab — Student'da fullName() metod,
    // User'da full_name ustun, ikkisi bir xil emas.
    protected $appends = ['borrower_name'];

    protected $fillable = [
        'book_id',
        'borrower_type',
        'borrower_id',
        'book_copy_id',
        'status',
        'notified_at',
        'expires_at',
        'fulfilled_loan_id',
        'cancelled_by',
        'cancel_reason',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'expires_at'  => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function borrower(): MorphTo
    {
        return $this->morphTo();
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function fulfilledLoan(): BelongsTo
    {
        return $this->belongsTo(BookLoan::class, 'fulfilled_loan_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getBorrowerNameAttribute(): ?string
    {
        $borrower = $this->borrower;

        if (! $borrower) {
            return null;
        }

        return $borrower instanceof Student
            ? $borrower->fullName()
            : $borrower->full_name;
    }

    /**
     * 'ready' holatida muddat o'tib ketganmi — ExpireLibraryReservations
     * buyrug'i shu orqali topadi. BookLoan::isOverdue()dagi bilan bir xil
     * naqsh: statik bayroq emas, har doim hisoblab chiqiladi.
     */
    public function isPickupExpired(?Carbon $now = null): bool
    {
        if ($this->status !== self::STATUS_READY || ! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isBefore($now ?? now());
    }
}
