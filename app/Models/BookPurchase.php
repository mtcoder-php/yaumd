<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPurchase extends Model
{
    protected $fillable = [
        'book_id', 'user_id', 'amount', 'provider',
        'transaction_id', 'status', 'provider_data', 'paid_at',
        'cancelled_at', 'cancel_reason',
        'payme_create_time', 'payme_perform_time', 'payme_cancel_time',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'provider_data' => 'array',
            'paid_at'       => 'datetime',
            'cancelled_at'  => 'datetime',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
