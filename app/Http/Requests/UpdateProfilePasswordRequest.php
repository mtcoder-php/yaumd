<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Joriy parolni kiriting',
            'password.required'         => 'Yangi parolni kiriting',
            'password.min'              => 'Parol kamida 8 ta belgi bo\'lishi kerak',
            'password.confirmed'        => 'Parollar mos kelmadi',
        ];
    }

    /**
     * "current_password" maydonini haqiqatan ham hisobning joriy paroliga
     * mosligini tekshiradi — oddiy validatsiya qoidalari (masalan
     * `current_password`) buni bevosita qila olmaydi, chunki bazadagi
     * qiymat bilan solishtirish kerak.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (
                $this->filled('current_password')
                && ! Hash::check($this->current_password, $this->user()->password)
            ) {
                $validator->errors()->add('current_password', 'Joriy parol noto\'g\'ri');
            }
        });
    }
}
