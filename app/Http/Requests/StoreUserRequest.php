<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|string|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'To\'liq ismni kiriting',
            'email.required'     => 'Email kiriting',
            'email.unique'       => 'Bu email allaqachon mavjud',
            'password.required'  => 'Parol kiriting',
            'password.min'       => 'Parol kamida 8 ta belgi bo\'lishi kerak',
            'password.confirmed' => 'Parollar mos kelmadi',
            'role.required'      => 'Rolni tanlang',
        ];
    }
}
