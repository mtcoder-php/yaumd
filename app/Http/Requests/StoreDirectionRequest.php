<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'faculty_id'     => 'required|exists:faculties,id',
            'department_id'  => 'nullable|exists:departments,id',
            'hemis_code'     => 'nullable|string|max:50',
            'name_uz'        => 'required|string|max:255',
            'name_ru'        => 'required|string|max:255',
            'name_en'        => 'nullable|string|max:255',
            'degree'         => 'required|in:bachelor,master',
            'duration_years' => 'required|integer|min:1|max:6',
            'quota_grant'    => 'nullable|integer|min:0',
            'quota_contract' => 'nullable|integer|min:0',
            'annual_fee'     => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'faculty_id.required' => 'Fakultetni tanlang',
            'name_uz.required'    => "O'zbek tilidagi nomi majburiy",
            'name_ru.required'    => 'Rus tilidagi nomi majburiy',
            'degree.required'     => "Darajani tanlang",
        ];
    }
}
