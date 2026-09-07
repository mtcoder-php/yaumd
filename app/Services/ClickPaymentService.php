<?php

namespace App\Services;

use App\Models\BookPurchase;

/**
 * Click.uz "Checkout" (klassik) merchant protokoli.
 *
 * Ishlash tartibi:
 * 1. Talaba "Click orqali to'lash" tugmasini bosadi -> buildCheckoutUrl()
 *    orqali hosil qilingan manzilga yo'naltiriladi.
 * 2. Talaba Click sahifasida to'laydi.
 * 3. Click bizning serverimizga IKKI marta POST so'rov yuboradi (action=0
 *    "Prepare", keyin action=1 "Complete") — buni ClickCallbackController
 *    qabul qiladi va shu klass orqali sign_string'ni tekshiradi.
 *
 * MUHIM: bu klass Click'ning rasmiy hujjatidagi (docs.click.uz) klassik
 * Checkout protokoliga asoslangan, lekin haqiqiy merchant hisob va test
 * kalitlar bilan hali sinovdan o'tkazilmagan — ishga tushirishdan oldin
 * Click test muhitida (yoki ularning sandbox'ida) real so'rov-javoblarni
 * solishtirib ko'rish tavsiya etiladi.
 */
class ClickPaymentService
{
    public const ERROR_SUCCESS = 0;
    public const ERROR_SIGN_FAILED = -1;
    public const ERROR_ALREADY_PAID = -4;
    public const ERROR_USER_NOT_FOUND = -5;
    public const ERROR_TRANSACTION_NOT_FOUND = -6;
    public const ERROR_TRANSACTION_CANCELLED = -9;

    public function buildCheckoutUrl(BookPurchase $purchase, string $returnUrl): string
    {
        $params = http_build_query([
            'service_id'       => config('services.click.service_id'),
            'merchant_id'      => config('services.click.merchant_id'),
            'amount'           => number_format((float) $purchase->amount, 2, '.', ''),
            'transaction_param' => $purchase->id,
            'return_url'       => $returnUrl,
        ]);

        return "https://my.click.uz/services/pay?{$params}";
    }

    /**
     * Prepare (action=0) va Complete (action=1) so'rovlari uchun umumiy
     * imzo tekshiruvi. $data — Click'dan kelgan barcha POST maydonlari.
     */
    public function verifySign(array $data): bool
    {
        $secret = (string) config('services.click.secret_key');
        $action = (int) ($data['action'] ?? -1);

        if ($action === 0) {
            $signString = ($data['click_trans_id'] ?? '')
                .($data['service_id'] ?? '')
                .$secret
                .($data['merchant_trans_id'] ?? '')
                .($data['amount'] ?? '')
                .($data['action'] ?? '')
                .($data['sign_time'] ?? '');
        } else {
            $signString = ($data['click_trans_id'] ?? '')
                .($data['service_id'] ?? '')
                .$secret
                .($data['merchant_trans_id'] ?? '')
                .($data['merchant_prepare_id'] ?? '')
                .($data['amount'] ?? '')
                .($data['action'] ?? '')
                .($data['sign_time'] ?? '');
        }

        return hash_equals(md5($signString), (string) ($data['sign_string'] ?? ''));
    }
}
