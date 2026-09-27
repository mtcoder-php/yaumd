<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Bitta kitob nusxasining bitta "berish" tarixi — kimga, qachon, qachongacha.
 * To'liq tushuntirish uchun migratsiyaga qarang.
 */
class BookLoan extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_LOST = 'lost';

    // Frontend (Show.vue) va Telegram xabarlari uchun — Student'da
    // fullName() metod, User'da esa full_name ustun, ikkisi bir xil emas,
    // shuning uchun bu yerda BITTA umumiy nom maydoni beriladi.
    protected $appends = ['borrower_name'];

    protected $fillable = [
        'book_copy_id',
        'borrower_type',
        'borrower_id',
        'issued_by',
        'returned_to',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function borrower(): MorphTo
    {
        return $this->morphTo();
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(LibraryLoanNotification::class);
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
     * Muddati o'tganmi — ATAYLAB saqlanmaydi, har doim shu paytdagi
     * 'due_date' bilan solishtirib hisoblanadi (ContractPaymentScheduleService
     * dagi "is_compliant" bilan bir xil mantiq: statik bayroq eskiradi,
     * hisoblangan qiymat esa hech qachon eskirmaydi).
     */
    public function isOverdue(?Carbon $now = null): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $now = $now ?? Carbon::today();

        return $this->due_date->lessThan($now->copy()->startOfDay());
    }

    public function daysOverdue(?Carbon $now = null): int
    {
        if (! $this->isOverdue($now)) {
            return 0;
        }

        $now = $now ?? Carbon::today();

        return (int) $this->due_date->diffInDays($now->copy()->startOfDay());
    }
}
