<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentGroup extends Model
{
    protected $fillable = [
        'direction_id', 'name',
        'academic_year', 'semester', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'group_id');
    }
}
