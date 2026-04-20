<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'faculty_id', 'hemis_code',
        'name_uz', 'name_ru', 'name_en',
        'degree', 'duration_years',
        'quota_grant', 'quota_contract',
        'annual_fee', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'annual_fee'   => 'decimal:2',
        ];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
