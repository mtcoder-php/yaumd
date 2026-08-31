<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $fillable = [
        'course_id', 'user_id', 'payment_type', 'payment_status',
        'amount', 'transaction_id', 'receipt', 'progress',
        'status', 'enrolled_at', 'completed_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at'  => 'datetime',
            'completed_at' => 'datetime',
            'expires_at'   => 'datetime',
            'amount'       => 'decimal:2',
            'progress'     => 'decimal:2',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
