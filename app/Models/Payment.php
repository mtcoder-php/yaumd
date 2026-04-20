<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'contract_id', 'user_id', 'amount',
        'provider', 'transaction_id', 'status',
        'provider_data', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'provider_data' => 'array',
            'paid_at'       => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
