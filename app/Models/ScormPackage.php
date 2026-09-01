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
     *
     * MUHIM: bu URL albatta JORIY so'rov (request) qaysi host bilan
     * ochilgan bo'lsa, o'sha host bilan qaytarilishi SHART — aks holda
     * (masalan .env'da APP_URL=http://localhost:8000 bo'lib, brauzer esa
     * http://127.0.0.1:8000 orqali kirsa) SCORM iframe'i talaba sahifasi
     * bilan boshqa-boshqa "origin" bo'lib qoladi, va SCORM kontenti
     * ichidagi JS window.parent.API'ga murojaat qilganda brauzer buni
     * xavfsizlik siyosati (Same-Origin Policy) bo'yicha bloklab,
     * "Permission denied to access property API on cross-origin object"
     * xatoligini beradi. Shuning uchun Storage'ning o'z (config'ga
     * asoslangan) URL'ini emas, joriy so'rovning host'ini ishlatamiz.
     */
    public function getFullLaunchUrlAttribute(): ?string
    {
        if (! $this->path || ! $this->launch_url) {
            return null;
        }

        $url = Storage::disk('public')->url(rtrim($this->path, '/').'/'.ltrim($this->launch_url, '/'));

        if (app()->bound('request') && request()) {
            $currentHost = request()->getSchemeAndHttpHost();
            $url = preg_replace('#^https?://[^/]+#', $currentHost, $url);
        }

        return $url;
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
