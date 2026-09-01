<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:8|confirmed',
            // Bitta foydalanuvchiga bir nechta rol biriktirilishi mumkin
            // (masalan "admin" + "moliya xodimi") — shuning uchun massiv.
            'roles'     => 'required|array|min:1',
            'roles.*'   => 'string|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'To\'liq ismni kiriting',
            'email.required'     => 'Email kiriting',
            'email.unique'       => 'Bu email allaqachon mavjud',
            'password.min'       => 'Parol kamida 8 ta belgi bo\'lishi kerak',
            'password.confirmed' => 'Parollar mos kelmadi',
            'roles.required'     => 'Kamida bitta rolni tanlang',
            'roles.min'          => 'Kamida bitta rolni tanlang',
        ];
    }
}
