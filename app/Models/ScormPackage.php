<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormPackage extends Model
{
    protected $fillable = [
        'title', 'version', 'path', 'launch_url',
        'identifier', 'manifest', 'file_size', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'manifest'  => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ScormAttempt::class);
    }
}
