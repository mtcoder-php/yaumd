<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class RequestEmailChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_email'         => 'required|email|max:255|unique:users,email,' . $this->user()->id,
            'current_password'  => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'new_email.required'        => 'Yangi email manzilni kiriting',
            'new_email.email'           => 'Email noto\'g\'ri formatda',
            'new_email.unique'          => 'Bu email allaqachon boshqa hisobda ishlatilgan',
            'current_password.required' => 'Joriy parolni kiriting',
        ];
    }

    /**
     * Email o'zgartirish shaxsiy hisob uchun jiddiy amal bo'lgani uchun
     * (kirish kodi shu manzilga bog'liq) — joriy parol bilan qo'shimcha
     * tasdiqlash talab qilinadi.
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
