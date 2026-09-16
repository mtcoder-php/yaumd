<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bitta Hikvision Face ID terminali (kirish yoki chiqish nuqtasi).
 *
 * @see \App\Services\HikvisionTerminalClient
 * @see \App\Console\Commands\SyncTurnstileEvents
 */
class TurnstileDevice extends Model
{
    protected $fillable = [
        'name',
        'scheme',
        'host',
        'port',
        'direction',
        'is_active',
        'last_synced_at',
        'last_serial_no',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(TurnstileEvent::class);
    }

    /**
     * Terminalga ISAPI so'rovlari uchun asosiy manzil, masalan:
     * "http://90.156.195.197:82".
     */
    public function baseUrl(): string
    {
        return "{$this->scheme}://{$this->host}:{$this->port}";
    }
}
