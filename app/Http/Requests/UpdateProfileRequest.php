<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:15|unique:users,phone,' . $this->user()->id,
            'address'    => 'nullable|string|max:1000',
            'birth_date' => 'nullable|date|before:today',
            'gender'     => 'nullable|in:male,female',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'To\'liq ismni kiriting',
            'phone.unique'       => 'Bu telefon raqam boshqa hisobda band',
            'birth_date.before'  => 'Tug\'ilgan sana noto\'g\'ri',
            'gender.in'          => 'Jinsni to\'g\'ri tanlang',
        ];
    }
}
