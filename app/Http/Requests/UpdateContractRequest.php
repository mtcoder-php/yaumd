<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'       => 'required|numeric|min:0',
            'payment_type' => 'required|in:grant,contract',
            'status'       => 'required|in:draft,signed,paid,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'       => 'Summani kiriting',
            'payment_type.required' => "To'lov turini tanlang",
            'status.required'       => 'Statusni tanlang',
        ];
    }
}
