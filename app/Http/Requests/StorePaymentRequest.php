<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_id'    => 'required|exists:contracts,id',
            'amount'         => 'required|numeric|min:1',
            // MUHIM: 'cash' (naqd) endi kassir tomonidan YANGI to'lov
            // qabul qilishda TANLAB bo'lmaydi — kontrakt naqd pulda
            // qabul qilinmaydi, faqat Click/Payme (onlayn) yoki
            // 'bank_receipt' (talaba/abituriyent naqd pulni bankka
            // borib Universitet hisob raqamiga o'tkazib, chekini
            // yuklagan holat) qabul qilinadi. 'cash' qiymati faqat
            // ESKI (shu tuzatishdan oldingi) yozuvlarni ko'rsatish
            // uchun bazada/enumda saqlab qolingan.
            'provider'       => 'required|in:click,payme,bank_receipt',
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id',
            // Bank cheki tanlanganda — /admin/payments/scan-receipt
            // orqali oldindan yuklab OCR qilingan chekning saqlangan
            // yo'li shu yerga (yashirin maydon sifatida) yuboriladi.
            // MUHIM (xavfsizlik): bu qiymat mijoz tomonidan yuboriladigan
            // oddiy matn bo'lgani uchun, "faqat scan-receipt o'zi
            // yaratgan, hali hech qaysi to'lovga bog'lanmagan fayl"
            // ekanligi withValidator() ichida qo'shimcha tekshiriladi —
            // aks holda kimdir boshqa (aloqasiz) faylning yo'lini
            // qo'lda yuborib, uni chek sifatida bog'lab qo'yishi mumkin.
            'receipt_path'   => ['required_if:provider,bank_receipt', 'nullable', 'string', 'max:255'],
            // OCR orqali o'qilgan xom matn — faqat audit/tekshirish
            // uchun saqlanadi, majburiy emas.
            'receipt_ocr_text' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'contract_id.required'     => 'Kontraktni tanlang',
            'amount.required'          => 'Summani kiriting',
            'amount.min'               => 'Summa 0 dan katta bo\'lishi kerak',
            'provider.required'        => "To'lov turini tanlang",
            'transaction_id.unique'    => 'Bu tranzaksiya ID allaqachon mavjud',
            'receipt_path.required_if' => 'Avval bank chekining suratini yuklang',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $path = $this->input('receipt_path');
            if (! $path) {
                return;
            }

            // Faqat scan-receipt endpointi yozadigan papka ichidagi,
            // haqiqatan mavjud va hali BOSHQA to'lovga bog'lanmagan
            // fayl qabul qilinadi.
            $isInsideReceiptsFolder = str_starts_with($path, 'payments/receipts/');
            $exists                 = $isInsideReceiptsFolder && Storage::disk('public')->exists($path);
            $alreadyUsed            = $exists && \App\Models\Payment::where('receipt_path', $path)->exists();

            if (! $exists || $alreadyUsed) {
                $validator->errors()->add('receipt_path', 'Chek fayli topilmadi — uni qaytadan yuklang');
            }
        });
    }
}
