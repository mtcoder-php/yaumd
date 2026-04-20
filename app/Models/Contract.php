<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'applicant_id', 'direction_id', 'contract_number',
        'amount', 'payment_type', 'status',
        'pdf_path', 'qr_code', 'otp_code',
        'otp_expires_at', 'signed_at',
    ];

    protected $hidden = ['otp_code'];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'otp_expires_at'  => 'datetime',
            'signed_at'       => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
