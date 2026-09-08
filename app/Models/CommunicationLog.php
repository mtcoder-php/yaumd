<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * CRM — abituriyent/talaba bilan bo'lgan bitta muloqot yozuvi (qo'ng'iroq,
 * email, uchrashuv yoki oddiy eslatma). SMS integratsiyasi hali yo'q — bu
 * yerda faqat qo'lda yozib qo'yiladigan tarix, avtomatik yuborish emas.
 */
class CommunicationLog extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'type', 'direction',
        'summary', 'occurred_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
