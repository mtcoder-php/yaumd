<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

/**
 * Bir martalik sozlash buyrug'i — serverga joylashtirilgandan keyin
 * Telegram'ga "yangilanishlarni shu manzilga yuborib tur" deb aytish
 * uchun. Masalan:
 *
 *   php artisan telegram:set-webhook https://yau.uz
 *
 * (URL oxiriga "/telegram/webhook/{secret}" avtomatik qo'shiladi —
 * TelegramService::setWebhook()ga qarang.)
 */
class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {base_url : Saytning asosiy manzili, masalan https://yau.uz}';

    protected $description = "Telegram bot webhook manzilini serverga ro'yxatdan o'tkazadi";

    public function handle(TelegramService $telegram): int
    {
        $secret = (string) config('services.telegram.webhook_secret');

        if ($secret === '') {
            $this->error('TELEGRAM_WEBHOOK_SECRET .env faylida sozlanmagan.');

            return self::FAILURE;
        }

        $result = $telegram->setWebhook($this->argument('base_url'), $secret);

        if ($result['ok'] ?? false) {
            $this->info("Webhook muvaffaqiyatli o'rnatildi: " . $this->argument('base_url') . "/telegram/webhook/{$secret}");

            return self::SUCCESS;
        }

        $this->error('Xatolik: ' . ($result['description'] ?? "noma'lum"));

        return self::FAILURE;
    }
}
