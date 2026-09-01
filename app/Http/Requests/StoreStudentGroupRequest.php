<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'direction_id'     => 'required|exists:directions,id',
            'department_id'    => 'nullable|exists:departments,id',
            'head_teacher_id'  => 'nullable|exists:users,id',
            'hemis_id'         => 'nullable|string|max:50|unique:student_groups,hemis_id',
            'name'             => [
                'required', 'string', 'max:50',
                Rule::unique('student_groups', 'name')
                    ->where(fn ($q) => $q->where('academic_year_id', $this->academic_year_id)),
            ],
            'degree'      => 'required|in:bachelor,master',
            'study_form'  => 'required|in:full_time,evening,distance',
            'course_year' => 'required|integer|min:1|max:6',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => "O'quv yilini tanlang",
            'academic_year_id.exists'   => "Tanlangan o'quv yili topilmadi",
            'direction_id.required'     => "Yo'nalishni tanlang",
            'direction_id.exists'       => "Tanlangan yo'nalish topilmadi",
            'name.required'             => 'Guruh nomi majburiy (masalan: MT-1-24)',
            'name.unique'               => 'Bu o\'quv yilida shunday nomdagi guruh allaqachon mavjud',
            'hemis_id.unique'           => 'Bunday HEMIS ID allaqachon mavjud',
            'degree.required'           => "Ta'lim darajasini tanlang",
            'study_form.required'       => "Ta'lim shaklini tanlang",
            'course_year.required'      => 'Kursni tanlang',
        ];
    }
}
