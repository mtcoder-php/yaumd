<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Click.uz — pullik elektron kitoblarni onlayn sotib olish uchun.
    // Qiymatlarni Click biznes kabinetingizdan (my.click.uz) olib,
    // serverdagi .env fayliga yozing — bu yerga hech qachon haqiqiy
    // qiymat yozilmaydi.
    'click' => [
        'service_id'       => env('CLICK_SERVICE_ID'),
        'merchant_id'      => env('CLICK_MERCHANT_ID'),
        'merchant_user_id' => env('CLICK_MERCHANT_USER_ID'),
        'secret_key'       => env('CLICK_SECRET_KEY'),
    ],

    // Payme (Paycom) — xuddi shu maqsadda. Test uchun checkout.test.paycom.uz
    // (PAYME_IS_TEST=true), haqiqiy ishga tushirishda checkout.paycom.uz
    // ishlatiladi.
    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'secret_key'  => env('PAYME_SECRET_KEY'),
        'is_test'     => env('PAYME_IS_TEST', false),
    ],

    // Telegram bot — talabalarga shartnoma to'lovi holati/eslatmalarini
    // yuborish uchun (TelegramService, TelegramWebhookController).
    // 'bot_token' — @BotFather'dan olinadi. 'webhook_secret' — o'zingiz
    // o'ylab topgan uzun tasodifiy satr (webhook manzilidagi maxfiy yo'l
    // segmenti sifatida ishlatiladi, hech kimga aytilmaydi).
    'telegram' => [
        'bot_token'      => env('TELEGRAM_BOT_TOKEN'),
        'bot_username'   => env('TELEGRAM_BOT_USERNAME'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    // Turniket (Face ID) tekshiruv API'sini himoya qiluvchi maxfiy kalit —
    // faqat turniket qurilmasi/serveri shu qiymatni "Authorization: Bearer
    // <token>" sarlavhasi bilan yuborishi kerak (TurnstileController).
    'turnstile' => [
        'api_token' => env('TURNSTILE_API_TOKEN'),
    ],

    // Hikvision Face ID terminallari (DS-K1T671MF, 12 dona) — barchasida
    // BIR XIL admin login/parol ishlatiladi (Mukhtor tomonidan tasdiqlangan),
    // shuning uchun markazlashtirilgan holda shu yerda saqlanadi. Haqiqiy
    // qiymat FAQAT serverdagi .env fayliga yoziladi, bu yerga yoki boshqa
    // hech qanday fayl/kodga yozilmasligi SHART.
    'hikvision' => [
        'username' => env('HIKVISION_USERNAME'),
        'password' => env('HIKVISION_PASSWORD'),
    ],

    // Bank cheki suratidan summani OCR orqali o'qish (ReceiptOcrService).
    // Standart holatda oddiy "tesseract" buyrug'i ishlatiladi — bu
    // dasturning PATH muhit o'zgaruvchisida ekanini talab qiladi. Agar
    // PATH orqali topilmasa (masalan Windows'da xizmat/service sifatida
    // ishlaydigan serverlar ko'pincha foydalanuvchi darajasidagi PATH
    // o'zgarishlarini KO'RMAYDI, hatto terminalda "tesseract --version"
    // ishlagan bo'lsa ham) — TESSERACT_BINARY orqali to'liq (absolyut)
    // yo'lni ko'rsating, masalan Windows'da:
    //   TESSERACT_BINARY="C:\Program Files\Tesseract-OCR\tesseract.exe"
    // yoki Linux'da:
    //   TESSERACT_BINARY=/usr/bin/tesseract
    'tesseract' => [
        'binary' => env('TESSERACT_BINARY', 'tesseract'),
    ],

];
