<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentOrder;
use App\Services\PaymePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Payme (Paycom) serverlaridan to'g'ridan-to'g'ri keladigan JSON-RPC 2.0
// so'rovlari — login talab qilinmaydi (o'z "Basic" autentifikatsiyasi bor),
// CSRF tekshiruvi ham o'chirilgan (bootstrap/app.php'ga qarang).
//
// Bitta umumiy endpoint ikki xil to'lovni ham qabul qiladi: kitob/kurs
// xaridi ('payment_orders' jadvali, 'ac.order_id' orqali) va shartnoma
// to'lovi ('payments' jadvali, 'ac.contract_payment_id' orqali) —
// PaymePaymentService::buildCheckoutUrl()'ga qarang.
class PaymeCallbackController extends Controller
{
    public function __construct(private PaymePaymentService $payme) {}

    public function callback(Request $request)
    {
        $id = $request->input('id');

        if (! $this->payme->checkAuth($request->header('Authorization'))) {
            return $this->error($id, PaymePaymentService::ERROR_INSUFFICIENT_PRIVILEGE, "Ruxsat berilmagan");
        }

        $method = $request->input('method');
        $params = $request->input('params', []);

        return match ($method) {
            'CheckPerformTransaction' => $this->checkPerformTransaction($id, $params),
            'CreateTransaction'       => $this->createTransaction($id, $params),
            'PerformTransaction'      => $this->performTransaction($id, $params),
            'CancelTransaction'       => $this->cancelTransaction($id, $params),
            'CheckTransaction'        => $this->checkTransaction($id, $params),
            'GetStatement'            => $this->getStatement($id, $params),
            default                   => $this->error($id, PaymePaymentService::ERROR_METHOD_NOT_FOUND, 'Metod topilmadi'),
        };
    }

    private function nowMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    private function result($id, array $result)
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
    }

    private function error($id, int $code, string $message)
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]]);
    }

    private function stateOf(PaymentOrder|Payment $transaction): int
    {
        return match (true) {
            $transaction->status === 'paid'                               => 2,
            $transaction->status === 'cancelled' && $transaction->paid_at => -2,
            $transaction->status === 'cancelled'                          => -1,
            default                                                       => 1,
        };
    }

    // Payme'ga bizning tarafimizdagi noyob (kolliziyasiz) havola — ikkita
    // turli jadvaldagi bir xil ID'lar ("payment_orders#5" va "payments#5")
    // Payme hisobotida chalkashib ketmasligi uchun prefiks qo'shiladi. Bu
    // faqat ko'rsatish uchun — biz hech qachon shu qiymat bo'yicha teskari
    // qidiruv qilmaymiz (har doim Payme'ning o'z transaction_id'si yoki
    // 'account' maydoni orqali qidiramiz).
    private function transactionRef(PaymentOrder|Payment $transaction): string
    {
        return ($transaction instanceof PaymentOrder ? 'order-' : 'contract-').$transaction->id;
    }

    // CreateTransaction/CheckPerformTransaction'da Payme checkout
    // havolasida biz bergan 'account' obyektini qaytaradi — qaysi maydon
    // kelganiga qarab qaysi jadvaldan qidirish kerakligi aniqlanadi.
    private function resolveByAccount(array $params): PaymentOrder|Payment|null
    {
        $account = $params['account'] ?? [];

        if (isset($account['order_id'])) {
            return PaymentOrder::find($account['order_id']);
        }

        if (isset($account['contract_payment_id'])) {
            return Payment::find($account['contract_payment_id']);
        }

        return null;
    }

    // PerformTransaction/CancelTransaction/CheckTransaction'da Payme faqat
    // O'ZINING transaction_id'sini beradi (bizning 'account'imizni emas) —
    // shu sababli avval 'payment_orders'dan, topilmasa 'payments'dan
    // qidiramiz. Bu ID Payme tomonidan yaratilgan global-noyob qiymat
    // bo'lgani uchun ikki jadval orasida kolliziya xavfi yo'q.
    private function resolveByPaymeId(?string $paymeId): PaymentOrder|Payment|null
    {
        if (! $paymeId) {
            return null;
        }

        return PaymentOrder::where('transaction_id', $paymeId)->first()
            ?? Payment::where('transaction_id', $paymeId)->first();
    }

    private function checkPerformTransaction($id, array $params)
    {
        $transaction = $this->resolveByAccount($params);

        if (! $transaction || $transaction->status === 'cancelled') {
            return $this->error($id, PaymePaymentService::ERROR_ORDER_NOT_FOUND, 'Buyurtma topilmadi');
        }

        $expectedTiyin = (int) round(((float) $transaction->amount) * 100);
        if ((int) ($params['amount'] ?? 0) !== $expectedTiyin) {
            return $this->error($id, PaymePaymentService::ERROR_INVALID_AMOUNT, "Summa noto'g'ri");
        }

        return $this->result($id, ['allow' => true]);
    }

    private function createTransaction($id, array $params)
    {
        $paymeId = $params['id'] ?? null;
        $transaction = $this->resolveByAccount($params);

        if (! $transaction) {
            return $this->error($id, PaymePaymentService::ERROR_ORDER_NOT_FOUND, 'Buyurtma topilmadi');
        }

        // Idempotentlik: Payme ba'zan bir xil so'rovni qayta yuboradi —
        // shu holatda bir xil javob qaytarilishi SHART.
        if ($transaction->transaction_id === $paymeId) {
            if ($transaction->status === 'cancelled') {
                return $this->error($id, PaymePaymentService::ERROR_COULD_NOT_PERFORM, 'Tranzaksiya bekor qilingan');
            }

            return $this->result($id, [
                'create_time' => $transaction->payme_create_time,
                'transaction' => $this->transactionRef($transaction),
                'state'       => $this->stateOf($transaction),
            ]);
        }

        if ($transaction->transaction_id) {
            return $this->error($id, PaymePaymentService::ERROR_COULD_NOT_PERFORM, 'Boshqa tranzaksiya allaqachon bog\'langan');
        }

        $expectedTiyin = (int) round(((float) $transaction->amount) * 100);
        if ((int) ($params['amount'] ?? 0) !== $expectedTiyin) {
            return $this->error($id, PaymePaymentService::ERROR_INVALID_AMOUNT, "Summa noto'g'ri");
        }

        $createTime = (int) ($params['time'] ?? $this->nowMs());

        $transaction->update([
            'transaction_id'    => $paymeId,
            'payme_create_time' => $createTime,
            'status'            => 'pending',
        ]);

        return $this->result($id, [
            'create_time' => $createTime,
            'transaction' => $this->transactionRef($transaction),
            'state'       => 1,
        ]);
    }

    private function performTransaction($id, array $params)
    {
        $transaction = $this->resolveByPaymeId($params['id'] ?? null);

        if (! $transaction) {
            return $this->error($id, PaymePaymentService::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        if ($transaction->status === 'paid') {
            return $this->result($id, [
                'transaction'  => $this->transactionRef($transaction),
                'perform_time' => $transaction->payme_perform_time,
                'state'        => 2,
            ]);
        }

        if ($transaction->status === 'cancelled') {
            return $this->error($id, PaymePaymentService::ERROR_COULD_NOT_PERFORM, 'Tranzaksiya bekor qilingan');
        }

        $performTime = $this->nowMs();

        DB::transaction(function () use ($transaction, $performTime) {
            $transaction->update([
                'status'             => 'paid',
                'paid_at'            => now(),
                'payme_perform_time' => $performTime,
            ]);

            $this->afterPaid($transaction);
        });

        return $this->result($id, [
            'transaction'  => $this->transactionRef($transaction),
            'perform_time' => $performTime,
            'state'        => 2,
        ]);
    }

    private function cancelTransaction($id, array $params)
    {
        $transaction = $this->resolveByPaymeId($params['id'] ?? null);

        if (! $transaction) {
            return $this->error($id, PaymePaymentService::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        if ($transaction->status !== 'cancelled') {
            $wasPaid = $transaction->status === 'paid';

            $transaction->update([
                'status'            => 'cancelled',
                'cancelled_at'      => now(),
                'cancel_reason'     => $params['reason'] ?? null,
                'payme_cancel_time' => $this->nowMs(),
            ]);

            if ($wasPaid) {
                $this->afterCancelPaid($transaction);
            }
        }

        return $this->result($id, [
            'transaction' => $this->transactionRef($transaction),
            'cancel_time' => $transaction->payme_cancel_time,
            'state'       => $this->stateOf($transaction),
        ]);
    }

    private function checkTransaction($id, array $params)
    {
        $transaction = $this->resolveByPaymeId($params['id'] ?? null);

        if (! $transaction) {
            return $this->error($id, PaymePaymentService::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        return $this->result($id, [
            'create_time'  => $transaction->payme_create_time,
            'perform_time' => $transaction->payme_perform_time ?? 0,
            'cancel_time'  => $transaction->payme_cancel_time ?? 0,
            'transaction'  => $this->transactionRef($transaction),
            'state'        => $this->stateOf($transaction),
            'reason'       => $transaction->cancel_reason,
        ]);
    }

    private function getStatement($id, array $params)
    {
        $from = (int) ($params['from'] ?? 0);
        $to = (int) ($params['to'] ?? 0);

        $orders = PaymentOrder::whereNotNull('transaction_id')
            ->whereBetween('payme_create_time', [$from, $to])
            ->get();

        $contractPayments = Payment::whereNotNull('transaction_id')
            ->whereBetween('payme_create_time', [$from, $to])
            ->get();

        $all = $orders->concat($contractPayments);

        return $this->result($id, [
            'transactions' => $all->map(fn ($t) => [
                'id'           => $t->transaction_id,
                'time'         => $t->payme_create_time,
                'amount'       => (int) round(((float) $t->amount) * 100),
                'account'      => $t instanceof PaymentOrder
                    ? ['order_id' => (string) $t->id]
                    : ['contract_payment_id' => (string) $t->id],
                'create_time'  => $t->payme_create_time,
                'perform_time' => $t->payme_perform_time ?? 0,
                'cancel_time'  => $t->payme_cancel_time ?? 0,
                'transaction'  => $this->transactionRef($t),
                'state'        => $this->stateOf($t),
                'reason'       => $t->cancel_reason,
            ])->values(),
        ]);
    }

    // To'lov muvaffaqiyatli bo'lganda: kitob/kurs bo'lsa ruxsat beriladi
    // (App\Contracts\Purchasable::grantAccessFor), shartnoma to'lovi
    // bo'lsa uning holati qayta hisoblanadi (Contract::refreshStatusFromPayments).
    private function afterPaid(PaymentOrder|Payment $transaction): void
    {
        if ($transaction instanceof PaymentOrder) {
            $transaction->payable?->grantAccessFor($transaction->user_id, $transaction);
        } else {
            $transaction->contract?->refreshStatusFromPayments();
        }
    }

    // To'langan tranzaksiya KEYINCHALIK bekor qilinganda (masalan bank
    // tomonidan qaytarilsa): kitob/kurs bo'lsa ruxsat qaytarib olinadi,
    // shartnoma bo'lsa "to'langan" holati "imzolangan"ga tushiriladi.
    private function afterCancelPaid(PaymentOrder|Payment $transaction): void
    {
        if ($transaction instanceof PaymentOrder) {
            $transaction->payable?->revokeAccessFor($transaction->user_id);
        } else {
            $transaction->contract?->refreshStatusFromPayments();
        }
    }
}
