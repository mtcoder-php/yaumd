<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_uz'   => 'required|string|max:255',
            'name_ru'   => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name_uz.required' => "O'zbek tilidagi nomi majburiy",
            'name_ru.required' => 'Rus tilidagi nomi majburiy',
        ];
    }
}
