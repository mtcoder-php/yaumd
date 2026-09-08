<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentOrder;
use App\Services\ClickPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Click.uz serverlaridan to'g'ridan-to'g'ri keladigan callback — login
// talab qilinmaydi, CSRF tekshiruvi ham o'chirilgan (bootstrap/app.php'ga
// qarang), chunki bu Click'ning o'z serveridan keladigan so'rov, brauzer
// sessiyasi bilan bog'liq emas.
//
// Bitta umumiy endpoint ikki xil to'lovni ham qabul qiladi: kitob/kurs
// xaridi ('payment_orders' jadvali) va shartnoma to'lovi ('payments'
// jadvali) — merchant_trans_id'dagi "order:"/"contract:" prefiksi orqali
// farqlanadi (ClickPaymentService::buildCheckoutUrl()'ga qarang).
class ClickCallbackController extends Controller
{
    public function __construct(private ClickPaymentService $click) {}

    public function callback(Request $request)
    {
        $data = $request->all();
        $action = (int) ($data['action'] ?? -1);
        $merchantTransId = (string) ($data['merchant_trans_id'] ?? '');

        if (! $this->click->verifySign($data)) {
            return response()->json([
                'error' => ClickPaymentService::ERROR_SIGN_FAILED,
                'error_note' => "Imzo (sign_string) noto'g'ri",
            ]);
        }

        $transaction = $this->resolveTransaction($merchantTransId);

        if (! $transaction) {
            return response()->json([
                'error' => ClickPaymentService::ERROR_TRANSACTION_NOT_FOUND,
                'error_note' => 'Buyurtma topilmadi',
            ]);
        }

        return $action === 0
            ? $this->prepare($data, $transaction, $merchantTransId)
            : $this->complete($data, $transaction, $merchantTransId);
    }

    private function resolveTransaction(string $merchantTransId): PaymentOrder|Payment|null
    {
        [$type, $id] = array_pad(explode(':', $merchantTransId, 2), 2, null);

        return match ($type) {
            'order'    => PaymentOrder::find($id),
            'contract' => Payment::find($id),
            default    => null,
        };
    }

    private function prepare(array $data, PaymentOrder|Payment $transaction, string $merchantTransId)
    {
        if ($transaction->status === 'paid') {
            return response()->json([
                'click_trans_id'    => $data['click_trans_id'] ?? null,
                'merchant_trans_id' => $merchantTransId,
                'error'             => ClickPaymentService::ERROR_ALREADY_PAID,
                'error_note'        => "Bu buyurtma allaqachon to'langan",
            ]);
        }

        if ((float) $transaction->amount !== round((float) ($data['amount'] ?? 0), 2)) {
            return response()->json([
                'click_trans_id'    => $data['click_trans_id'] ?? null,
                'merchant_trans_id' => $merchantTransId,
                'error'             => -2,
                'error_note'        => "Summa mos kelmadi",
            ]);
        }

        return response()->json([
            'click_trans_id'      => $data['click_trans_id'] ?? null,
            'merchant_trans_id'   => $merchantTransId,
            'merchant_prepare_id' => $transaction->id,
            'error'               => ClickPaymentService::ERROR_SUCCESS,
            'error_note'          => 'Success',
        ]);
    }

    private function complete(array $data, PaymentOrder|Payment $transaction, string $merchantTransId)
    {
        // Click o'z tomonida xatolik/bekor qilishni ham "Complete"
        // so'rovi ichida error maydoni orqali bildiradi (masalan
        // foydalanuvchi kartasi rad etilgan bo'lsa).
        if ((int) ($data['error'] ?? 0) < 0) {
            $transaction->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(),
                'provider_data' => $data,
            ]);

            $this->afterCancel($transaction);

            return response()->json([
                'click_trans_id'      => $data['click_trans_id'] ?? null,
                'merchant_trans_id'   => $merchantTransId,
                'merchant_confirm_id' => $transaction->id,
                'error'               => ClickPaymentService::ERROR_TRANSACTION_CANCELLED,
                'error_note'          => 'Bekor qilindi',
            ]);
        }

        if ($transaction->status !== 'paid') {
            DB::transaction(function () use ($transaction, $data) {
                $transaction->update([
                    'status'         => 'paid',
                    'transaction_id' => $data['click_trans_id'] ?? $transaction->transaction_id,
                    'paid_at'        => now(),
                    'provider_data'  => $data,
                ]);

                $this->afterPaid($transaction);
            });
        }

        return response()->json([
            'click_trans_id'      => $data['click_trans_id'] ?? null,
            'merchant_trans_id'   => $merchantTransId,
            'merchant_confirm_id' => $transaction->id,
            'error'               => ClickPaymentService::ERROR_SUCCESS,
            'error_note'          => 'Success',
        ]);
    }

    // To'lov muvaffaqiyatli tasdiqlanganda: kitob/kurs bo'lsa ruxsat
    // beriladi (App\Contracts\Purchasable::grantAccessFor), shartnoma
    // to'lovi bo'lsa uning holati qayta hisoblanadi
    // (Contract::refreshStatusFromPayments — to'liq to'langan bo'lsa
    // shartnoma "to'langan" deb belgilanadi).
    private function afterPaid(PaymentOrder|Payment $transaction): void
    {
        if ($transaction instanceof PaymentOrder) {
            $transaction->payable?->grantAccessFor($transaction->user_id, $transaction);
        } else {
            $transaction->contract?->refreshStatusFromPayments();
        }
    }

    private function afterCancel(PaymentOrder|Payment $transaction): void
    {
        // Click uchun bekor qilish "Complete" bosqichida (hali "paid"
        // bo'lmagan holatda) sodir bo'ladi, shuning uchun odatda hech
        // qanday ruxsat/holat o'zgarmagan bo'ladi — shartnoma uchun
        // baribir xavfsizlik maqsadida qayta hisoblanadi (idempotent).
        if ($transaction instanceof Payment) {
            $transaction->contract?->refreshStatusFromPayments();
        }
    }
}
