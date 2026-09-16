<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SendPaymentReminders buyrug'i qaysi kontraktga, qaysi oy uchun, qaysi
 * turdagi Telegram xabari yuborilganini qayd qiladi — takroriy
 * yuborishning oldini olish uchun (migratsiyadagi unique cheklovga qarang).
 */
class PaymentNotification extends Model
{
    public const TYPE_REMINDER_BEFORE   = 'reminder_before';
    public const TYPE_DEADLINE_TODAY    = 'deadline_today';
    public const TYPE_OVERDUE           = 'overdue';
    public const TYPE_PAYMENT_CONFIRMED = 'payment_confirmed';

    protected $fillable = [
        'contract_id', 'type', 'period', 'week', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'period'  => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
