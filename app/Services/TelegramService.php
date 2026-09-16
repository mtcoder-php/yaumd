<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Bot API bilan ishlash uchun yupqa (thin) xizmat — hech qanday
 * tashqi paket (composer kutubxonasi) talab qilinmaydi, Laravel'ning o'z
 * Http mijozi orqali to'g'ridan-to'g'ri so'rov yuboriladi.
 *
 * MUHIM: TELEGRAM_BOT_TOKEN .env'da sozlanmagan bo'lsa, xabar yuborish
 * jimgina o'chirilgan holatda ishlaydi (log'ga yoziladi, xatolik
 * tashlanmaydi) — shu tufayli hali bot ulanmagan muhitda ham (masalan
 * test/sinov paytida) qolgan tizim (to'lov qabul qilish va h.k.) buzilib
 * qolmaydi.
 */
class TelegramService
{
    private ?string $token;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->token);
    }

    /**
     * Oddiy matnli xabar yuboradi. $keyboard berilsa, Telegram'ning "reply
     * keyboard" tugmalari qo'shiladi (masalan "Telefon raqamni ulashish").
     *
     * @param array<int, array<int, array<string, mixed>>>|null $keyboard
     */
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null): bool
    {
        if (! $this->isConfigured()) {
            Log::info('Telegram: bot token sozlanmagan, xabar yuborilmadi.', ['chat_id' => $chatId]);

            return false;
        }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $payload['reply_markup'] = json_encode([
                'keyboard'          => $keyboard,
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]);
        } else {
            // Klaviatura yuborilmasa ham, avvalgi maxsus klaviaturani
            // yopib qo'yamiz (masalan "telefon ulashish" tugmasi ishlatib
            // bo'lingach) — aks holda talaba ekranida keraksiz tugma
            // osilib qolaveradi.
            $payload['reply_markup'] = json_encode(['remove_keyboard' => true]);
        }

        try {
            $response = Http::timeout(8)
                ->asForm()
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

            if (! $response->successful()) {
                Log::warning('Telegram: sendMessage muvaffaqiyatsiz.', [
                    'chat_id' => $chatId,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            // Telegram API'ga tarmoq orqali yeta olmaslik (yoki boshqa
            // kutilmagan xato) hech qachon asosiy oqimni (to'lov qabul
            // qilish, kunlik eslatma buyrug'i va h.k.) to'xtatib qo'ymasligi
            // kerak — shuning uchun bu yerda faqat log yoziladi.
            Log::error('Telegram: sendMessage istisno holati.', [
                'chat_id' => $chatId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Webhook manzilini Telegram serverlariga ro'yxatdan o'tkazadi —
     * TelegramSetWebhook buyrug'i orqali bir martalik sozlashda ishlatiladi.
     */
    public function setWebhook(string $url, string $secret): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'description' => 'TELEGRAM_BOT_TOKEN sozlanmagan'];
        }

        $response = Http::timeout(10)->asForm()->post(
            "https://api.telegram.org/bot{$this->token}/setWebhook",
            ['url' => rtrim($url, '/') . '/telegram/webhook/' . $secret]
        );

        return $response->json() ?? ['ok' => false, 'description' => 'Javob bo\'sh'];
    }
}
