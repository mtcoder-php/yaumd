<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name_uz', 'name_ru', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function directionSubjects(): HasMany
    {
        return $this->hasMany(DirectionSubject::class);
    }
}
