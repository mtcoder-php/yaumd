<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'user_id', 'amount',
        'provider', 'transaction_id', 'status',
        'provider_data', 'paid_at',
        // Talaba shartnomasini Click/Payme orqali ONLAYN to'lashi uchun
        // qo'shilgan maydonlar (avval faqat moliya xodimi qo'lda 'cash'
        // to'lov kiritganda ishlatilmasdi) — 'payment_orders' jadvalidagi
        // bilan bir xil ma'noda (ContractClickCallbackController /
        // ContractPaymeCallbackController'ga qarang).
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

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
