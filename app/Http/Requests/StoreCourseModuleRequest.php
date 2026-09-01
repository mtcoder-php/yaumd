<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_uz'     => 'required|string|max:255',
            'title_ru'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'order'        => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title_uz.required' => 'Modul nomi majburiy',
        ];
    }
}
