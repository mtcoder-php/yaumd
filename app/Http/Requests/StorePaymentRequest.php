<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'provider'       => 'required|in:click,payme,cash',
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id',
        ];
    }

    public function messages(): array
    {
        return [
            'contract_id.required'    => 'Kontraktni tanlang',
            'amount.required'         => 'Summani kiriting',
            'amount.min'              => 'Summa 0 dan katta bo\'lishi kerak',
            'provider.required'       => "To'lov turini tanlang",
            'transaction_id.unique'   => 'Bu tranzaksiya ID allaqachon mavjud',
        ];
    }
}
