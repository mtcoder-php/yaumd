<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BookPurchase;
use App\Models\LibraryAccess;
use App\Services\ClickPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Click.uz serverlaridan to'g'ridan-to'g'ri keladigan callback — login
// talab qilinmaydi, CSRF tekshiruvi ham o'chirilgan (bootstrap/app.php'ga
// qarang), chunki bu Click'ning o'z serveridan keladigan so'rov, brauzer
// sessiyasi bilan bog'liq emas.
class ClickCallbackController extends Controller
{
    public function __construct(private ClickPaymentService $click) {}

    public function callback(Request $request)
    {
        $data = $request->all();
        $action = (int) ($data['action'] ?? -1);
        $merchantTransId = $data['merchant_trans_id'] ?? null;

        if (! $this->click->verifySign($data)) {
            return response()->json([
                'error' => ClickPaymentService::ERROR_SIGN_FAILED,
                'error_note' => "Imzo (sign_string) noto'g'ri",
            ]);
        }

        $purchase = BookPurchase::find($merchantTransId);

        if (! $purchase) {
            return response()->json([
                'error' => ClickPaymentService::ERROR_TRANSACTION_NOT_FOUND,
                'error_note' => 'Buyurtma topilmadi',
            ]);
        }

        return $action === 0
            ? $this->prepare($data, $purchase)
            : $this->complete($data, $purchase);
    }

    private function prepare(array $data, BookPurchase $purchase)
    {
        if ($purchase->status === 'paid') {
            return response()->json([
                'click_trans_id'    => $data['click_trans_id'] ?? null,
                'merchant_trans_id' => $purchase->id,
                'error'             => ClickPaymentService::ERROR_ALREADY_PAID,
                'error_note'        => "Bu buyurtma allaqachon to'langan",
            ]);
        }

        if ((float) $purchase->amount !== round((float) ($data['amount'] ?? 0), 2)) {
            return response()->json([
                'click_trans_id'    => $data['click_trans_id'] ?? null,
                'merchant_trans_id' => $purchase->id,
                'error'             => -2,
                'error_note'        => "Summa mos kelmadi",
            ]);
        }

        return response()->json([
            'click_trans_id'      => $data['click_trans_id'] ?? null,
            'merchant_trans_id'   => $purchase->id,
            'merchant_prepare_id' => $purchase->id,
            'error'               => ClickPaymentService::ERROR_SUCCESS,
            'error_note'          => 'Success',
        ]);
    }

    private function complete(array $data, BookPurchase $purchase)
    {
        // Click o'z tomonida xatolik/bekor qilishni ham "Complete"
        // so'rovi ichida error maydoni orqali bildiradi (masalan foydalanuvchi
        // kartasi rad etilgan bo'lsa).
        if ((int) ($data['error'] ?? 0) < 0) {
            $purchase->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(),
                'provider_data' => $data,
            ]);

            return response()->json([
                'click_trans_id'      => $data['click_trans_id'] ?? null,
                'merchant_trans_id'   => $purchase->id,
                'merchant_confirm_id' => $purchase->id,
                'error'               => ClickPaymentService::ERROR_TRANSACTION_CANCELLED,
                'error_note'          => 'Bekor qilindi',
            ]);
        }

        if ($purchase->status !== 'paid') {
            DB::transaction(function () use ($purchase, $data) {
                $purchase->update([
                    'status'         => 'paid',
                    'transaction_id' => $data['click_trans_id'] ?? $purchase->transaction_id,
                    'paid_at'        => now(),
                    'provider_data'  => $data,
                ]);

                LibraryAccess::firstOrCreate([
                    'user_id' => $purchase->user_id,
                    'book_id' => $purchase->book_id,
                ], [
                    'purchase_id' => $purchase->id,
                    'access_type' => 'purchased',
                ]);
            });
        }

        return response()->json([
            'click_trans_id'      => $data['click_trans_id'] ?? null,
            'merchant_trans_id'   => $purchase->id,
            'merchant_confirm_id' => $purchase->id,
            'error'               => ClickPaymentService::ERROR_SUCCESS,
            'error_note'          => 'Success',
        ]);
    }
}
