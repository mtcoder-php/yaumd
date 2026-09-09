<?php

namespace App\Http\Requests;

use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_amount'      => 'required|numeric|min:0',
            'payment_type'     => 'required|in:grant,contract',
            'status'           => 'required|in:draft,signed,paid,cancelled',
            'discount_percent' => ['nullable', Rule::in(Contract::DISCOUNT_PERCENTS)],
            'discount_reason'  => [
                'nullable',
                Rule::in(array_keys(Contract::DISCOUNT_REASONS)),
                'required_if:discount_percent,10,20,25,50,75,100',
            ],
            'discount_note'    => [
                'nullable', 'string', 'max:500',
                'required_if:discount_reason,other',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'base_amount.required'     => 'Summani kiriting',
            'payment_type.required'    => "To'lov turini tanlang",
            'status.required'          => 'Statusni tanlang',
            'discount_reason.required_if' => 'Chegirma sababini tanlang',
            'discount_note.required_if'   => "\"Boshqa\" sababi uchun izoh yozing",
        ];
    }
}
