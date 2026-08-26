<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacultyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name_uz'        => 'required|string|max:255',
            'name_ru'        => 'required|string|max:255',
            'name_en'        => 'nullable|string|max:255',
            'short_name'     => 'required|string|max:50',
            'dean_id'        => 'nullable|exists:users,id',
            'description_uz' => 'nullable|string',
            'description_ru' => 'nullable|string',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name_uz.required'    => "O'zbek tilidagi nomi majburiy",
            'name_ru.required'    => 'Rus tilidagi nomi majburiy',
            'short_name.required' => 'Qisqa nomi majburiy',
        ];
    }
}
