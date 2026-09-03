<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_uz'   => 'required|string|max:255',
            'name_ru'   => 'nullable|string|max:255',
            'name_en'   => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name_uz.required' => "Kategoriya nomi majburiy",
        ];
    }
}
