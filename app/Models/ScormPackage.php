<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ScormPackage extends Model
{
    protected $appends = ['full_launch_url'];

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

    /**
     * Brauzerda to'g'ridan-to'g'ri ochiladigan, paket ichidagi ishga
     * tushirish faylining to'liq URL'i (masalan
     * ".../storage/scorm/{uuid}/scormcontent/index.html").
     */
    public function getFullLaunchUrlAttribute(): ?string
    {
        if (! $this->path || ! $this->launch_url) {
            return null;
        }

        return Storage::disk('public')->url(rtrim($this->path, '/').'/'.ltrim($this->launch_url, '/'));
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
