<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacultyRequest extends FormRequest
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
}
