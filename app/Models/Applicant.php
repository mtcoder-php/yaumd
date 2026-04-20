<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'application_number',
        'full_name', 'passport_series', 'jshshir',
        'birth_date', 'gender', 'nationality',
        'phone', 'email', 'address',
        'region_id', 'district_id',
        'education_type', 'direction_id', 'status',
        'interview_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'   => 'date',
            'interview_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function testSession(): HasOne
    {
        return $this->hasOne(TestSession::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }
}
