<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    // Frontendga chekning HAQIQIY yo'li emas, to'g'ridan-to'g'ri
    // ko'rsatsa/ochsa bo'ladigan URL beriladi — LibraryBook'dagi
    // cover_image_url bilan bir xil naqsh.
    protected $appends = ['receipt_url'];

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
        // Bank cheki surati (provider='bank_receipt') va undan OCR
        // orqali o'qilgan xom matn — ReceiptOcrService'ga qarang.
        'receipt_path', 'receipt_ocr_text',
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

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? Storage::disk('public')->url($this->receipt_path) : null;
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
