<?php

namespace App\Services;

use App\Models\BookPurchase;

/**
 * Payme (Paycom) "Merchant Cash Register" JSON-RPC 2.0 protokoli.
 *
 * Payme'ning o'z serveridan bitta umumiy endpoint'ga (PaymeCallbackController)
 * quyidagi metodlar bilan so'rov keladi: CheckPerformTransaction,
 * CreateTransaction, PerformTransaction, CancelTransaction, CheckTransaction,
 * GetStatement. Har bir so'rov "Basic" autentifikatsiya bilan keladi (login
 * har doim "Paycom", parol — merchant maxfiy kaliti).
 *
 * MUHIM: bu klass Payme'ning rasmiy hujjatidagi (developer.help.paycom.uz)
 * standart protokoliga asoslangan, lekin haqiqiy merchant hisob bilan hali
 * sinovdan o'tkazilmagan — ishga tushirishdan oldin Payme TEST muhitida
 * (checkout.test.paycom.uz) tekshirib ko'rish tavsiya etiladi.
 */
class PaymePaymentService
{
    // Payme rasmiy xato kodlari (developer.help.paycom.uz)
    public const ERROR_INVALID_AMOUNT = -31001;
    public const ERROR_TRANSACTION_NOT_FOUND = -31003;
    public const ERROR_COULD_NOT_PERFORM = -31008;
    public const ERROR_COULD_NOT_CANCEL = -31007;
    public const ERROR_ORDER_NOT_FOUND = -31050;
    public const ERROR_INSUFFICIENT_PRIVILEGE = -32504;
    public const ERROR_METHOD_NOT_FOUND = -32601;

    public function buildCheckoutUrl(BookPurchase $purchase, string $returnUrl): string
    {
        $amountTiyin = (int) round(((float) $purchase->amount) * 100);

        $params = "m=".config('services.payme.merchant_id')
            .";ac.order_id={$purchase->id}"
            .";a={$amountTiyin}"
            .";c={$returnUrl}";

        $host = config('services.payme.is_test') ? 'checkout.test.paycom.uz' : 'checkout.paycom.uz';

        return "https://{$host}/".base64_encode($params);
    }

    /**
     * Payme so'rovidagi "Authorization: Basic ..." sarlavhasini tekshiradi.
     * Login har doim "Paycom", parol — merchant maxfiy kaliti (yoki test
     * kaliti, PAYME_IS_TEST=true bo'lsa).
     */
    public function checkAuth(?string $authHeader): bool
    {
        if (! $authHeader || ! str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $decoded = base64_decode(substr($authHeader, 6));
        [$login, $password] = array_pad(explode(':', (string) $decoded, 2), 2, '');

        return $login === 'Paycom' && hash_equals((string) config('services.payme.secret_key'), $password);
    }
}
