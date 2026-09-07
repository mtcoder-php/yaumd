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

];
