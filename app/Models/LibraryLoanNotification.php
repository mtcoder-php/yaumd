<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryLoanNotification extends Model
{
    public const TYPE_ISSUED = 'issued';
    public const TYPE_REMINDER_BEFORE = 'reminder_before';
    public const TYPE_OVERDUE = 'overdue';
    public const TYPE_RETURNED = 'returned';

    protected $fillable = [
        'book_loan_id',
        'type',
        'week',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function bookLoan(): BelongsTo
    {
        return $this->belongsTo(BookLoan::class);
    }
}
