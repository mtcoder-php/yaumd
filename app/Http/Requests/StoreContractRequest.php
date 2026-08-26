<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'applicant_id' => 'required|exists:applicants,id',
            'direction_id' => 'required|exists:directions,id',
            'amount'       => 'required|numeric|min:0',
            'payment_type' => 'required|in:grant,contract',
        ];
    }

    public function messages(): array
    {
        return [
            'applicant_id.required' => 'Abituriyentni tanlang',
            'direction_id.required' => "Yo'nalishni tanlang",
            'amount.required'       => 'Summani kiriting',
            'payment_type.required' => "To'lov turini tanlang",
        ];
    }
}
