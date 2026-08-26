<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'faculty_id' => 'required|exists:faculties,id',
            'name_uz'    => 'required|string|max:255',
            'name_ru'    => 'required|string|max:255',
            'name_en'    => 'nullable|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'head_id'    => 'nullable|exists:users,id',
            'is_active'  => 'boolean',
        ];
    }
}
