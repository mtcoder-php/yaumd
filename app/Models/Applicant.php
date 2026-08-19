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
        'user_id',
        'application_number',
        'education_type',
        'study_form',
        'direction_id',
        'first_name',
        'last_name',
        'middle_name',
        'birth_year',
        'birth_month',
        'birth_day',
        'gender',
        'nationality',
        'passport_series',
        'jshshir',
        'phone',
        'extra_phone',
        'email',
        'region_id',
        'district_id',
        'address',
        'previous_diploma',
        'previous_edu_place',
        'status',
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
