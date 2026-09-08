<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

// Click/Payme orqali sotib olingan/sotib olinayotgan HAR QANDAY narsa
// (elektron kitob, pullik kurs...) uchun umumiy to'lov buyurtmasi.
// 'payable' — polimorfik bog'lanish, App\Contracts\Purchasable interfeysini
// amalga oshiruvchi istalgan modelga (LibraryBook, Course) ishora qiladi.
class PaymentOrder extends Model
{
    protected $fillable = [
        'payable_type', 'payable_id', 'user_id',
        'amount', 'provider', 'transaction_id', 'status',
        'provider_data', 'paid_at', 'cancelled_at', 'cancel_reason',
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

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
