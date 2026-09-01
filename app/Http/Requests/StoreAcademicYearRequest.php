<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:20|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => "O'quv yili nomi majburiy (masalan: 2026-2027)",
            'name.unique'         => 'Bunday nomdagi o\'quv yili allaqachon mavjud',
            'start_date.required' => 'Boshlanish sanasi majburiy',
            'end_date.required'   => 'Tugash sanasi majburiy',
            'end_date.after'      => 'Tugash sanasi boshlanish sanasidan keyin bo\'lishi kerak',
        ];
    }
}
