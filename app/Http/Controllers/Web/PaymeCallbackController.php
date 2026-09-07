<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BookPurchase;
use App\Models\LibraryAccess;
use App\Services\PaymePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Payme (Paycom) serverlaridan to'g'ridan-to'g'ri keladigan JSON-RPC 2.0
// so'rovlari — login talab qilinmaydi (o'z "Basic" autentifikatsiyasi bor),
// CSRF tekshiruvi ham o'chirilgan (bootstrap/app.php'ga qarang).
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

    private function stateOf(BookPurchase $p): int
    {
        return match (true) {
            $p->status === 'paid'                          => 2,
            $p->status === 'cancelled' && $p->paid_at       => -2,
            $p->status === 'cancelled'                      => -1,
            default                                         => 1,
        };
    }

    private function checkPerformTransaction($id, array $params)
    {
        $purchase = BookPurchase::find($params['account']['order_id'] ?? null);

        if (! $purchase || $purchase->status === 'cancelled') {
            return $this->error($id, PaymePaymentService::ERROR_ORDER_NOT_FOUND, 'Buyurtma topilmadi');
        }

        $expectedTiyin = (int) round(((float) $purchase->amount) * 100);
        if ((int) ($params['amount'] ?? 0) !== $expectedTiyin) {
            return $this->error($id, PaymePaymentService::ERROR_INVALID_AMOUNT, "Summa noto'g'ri");
        }

        return $this->result($id, ['allow' => true]);
    }

    private function createTransaction($id, array $params)
    {
        $paymeId = $params['id'] ?? null;
        $purchase = BookPurchase::find($params['account']['order_id'] ?? null);

        if (! $purchase) {
            return $this->error($id, PaymePaymentService::ERROR_ORDER_NOT_FOUND, 'Buyurtma topilmadi');
        }

        // Idempotentlik: Payme ba'zan bir xil so'rovni qayta yuboradi —
        // shu holatda bir xil javob qaytarilishi SHART.
        if ($purchase->transaction_id === $paymeId) {
            if ($purchase->status === 'cancelled') {
                return $this->error($id, PaymePaymentService::ERROR_COULD_NOT_PERFORM, 'Tranzaksiya bekor qilingan');
            }

            return $this->result($id, [
                'create_time' => $purchase->payme_create_time,
                'transaction' => (string) $purchase->id,
                'state'       => $this->stateOf($purchase),
            ]);
        }

        if ($purchase->transaction_id) {
            return $this->error($id, PaymePaymentService::ERROR_COULD_NOT_PERFORM, 'Boshqa tranzaksiya allaqachon bog\'langan');
        }

        $expectedTiyin = (int) round(((float) $purchase->amount) * 100);
        if ((int) ($params['amount'] ?? 0) !== $expectedTiyin) {
            return $this->error($id, PaymePaymentService::ERROR_INVALID_AMOUNT, "Summa noto'g'ri");
        }

        $createTime = (int) ($params['time'] ?? $this->nowMs());

        $purchase->update([
            'transaction_id'     => $paymeId,
            'payme_create_time'  => $createTime,
            'status'             => 'pending',
        ]);

        return $this->result($id, [
            'create_time' => $createTime,
            'transaction' => (string) $purchase->id,
            'state'       => 1,
        ]);
    }

    private function performTransaction($id, array $params)
    {
        $purchase = BookPurchase::where('transaction_id', $params['id'] ?? null)->first();

        if (! $purchase) {
            return $this->error($id, PaymePaymentService::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        if ($purchase->status === 'paid') {
            return $this->result($id, [
                'transaction'  => (string) $purchase->id,
                'perform_time' => $purchase->payme_perform_time,
                'state'        => 2,
            ]);
        }

        if ($purchase->status === 'cancelled') {
            return $this->error($id, PaymePaymentService::ERROR_COULD_NOT_PERFORM, 'Tranzaksiya bekor qilingan');
        }

        $performTime = $this->nowMs();

        DB::transaction(function () use ($purchase, $performTime) {
            $purchase->update([
                'status'             => 'paid',
                'paid_at'            => now(),
                'payme_perform_time' => $performTime,
            ]);

            LibraryAccess::firstOrCreate([
                'user_id' => $purchase->user_id,
                'book_id' => $purchase->book_id,
            ], [
                'purchase_id' => $purchase->id,
                'access_type' => 'purchased',
            ]);
        });

        return $this->result($id, [
            'transaction'  => (string) $purchase->id,
            'perform_time' => $performTime,
            'state'        => 2,
        ]);
    }

    private function cancelTransaction($id, array $params)
    {
        $purchase = BookPurchase::where('transaction_id', $params['id'] ?? null)->first();

        if (! $purchase) {
            return $this->error($id, PaymePaymentService::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        if ($purchase->status !== 'cancelled') {
            $wasPaid = $purchase->status === 'paid';

            $purchase->update([
                'status'             => 'cancelled',
                'cancelled_at'       => now(),
                'cancel_reason'      => $params['reason'] ?? null,
                'payme_cancel_time'  => $this->nowMs(),
            ]);

            if ($wasPaid) {
                LibraryAccess::where('purchase_id', $purchase->id)->delete();
            }
        }

        return $this->result($id, [
            'transaction' => (string) $purchase->id,
            'cancel_time' => $purchase->payme_cancel_time,
            'state'       => $this->stateOf($purchase),
        ]);
    }

    private function checkTransaction($id, array $params)
    {
        $purchase = BookPurchase::where('transaction_id', $params['id'] ?? null)->first();

        if (! $purchase) {
            return $this->error($id, PaymePaymentService::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        return $this->result($id, [
            'create_time'  => $purchase->payme_create_time,
            'perform_time' => $purchase->payme_perform_time ?? 0,
            'cancel_time'  => $purchase->payme_cancel_time ?? 0,
            'transaction'  => (string) $purchase->id,
            'state'        => $this->stateOf($purchase),
            'reason'       => $purchase->cancel_reason,
        ]);
    }

    private function getStatement($id, array $params)
    {
        $from = (int) ($params['from'] ?? 0);
        $to   = (int) ($params['to'] ?? 0);

        $purchases = BookPurchase::whereNotNull('transaction_id')
            ->whereBetween('payme_create_time', [$from, $to])
            ->get();

        return $this->result($id, [
            'transactions' => $purchases->map(fn ($p) => [
                'id'           => $p->transaction_id,
                'time'         => $p->payme_create_time,
                'amount'       => (int) round(((float) $p->amount) * 100),
                'account'      => ['order_id' => (string) $p->id],
                'create_time'  => $p->payme_create_time,
                'perform_time' => $p->payme_perform_time ?? 0,
                'cancel_time'  => $p->payme_cancel_time ?? 0,
                'transaction'  => (string) $p->id,
                'state'        => $this->stateOf($p),
                'reason'       => $p->cancel_reason,
            ])->values(),
        ]);
    }
}
