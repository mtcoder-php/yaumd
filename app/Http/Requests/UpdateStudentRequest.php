<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'direction_id'      => 'required|exists:directions,id',
            'department_id'     => 'nullable|exists:departments,id',
            'hemis_id'          => 'nullable|string|max:50|unique:students,hemis_id,' . $id,
            'student_number'    => 'nullable|string|max:20|unique:students,student_number,' . $id,
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'middle_name'       => 'nullable|string|max:100',
            'passport_series'   => 'nullable|string|max:20',
            'jshshir'           => 'nullable|digits:14',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:100',
            'birth_day'         => 'nullable|integer|between:1,31',
            'birth_month'       => 'nullable|integer|between:1,12',
            'birth_year'        => 'nullable|integer|between:1950,' . date('Y'),
            'gender'            => 'nullable|in:male,female',
            'degree'            => 'required|in:bachelor,master',
            'study_form'        => 'required|in:full_time,evening,distance',
            'course_year'       => 'required|integer|between:1,6',
            'status'            => 'required|in:active,academic_leave,expelled,graduated,transferred',
            'funding_type'      => 'required|in:grant,contract',
            'address'           => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => "O'quv yilini tanlang",
            'direction_id.required'     => "Yo'nalishni tanlang",
            'first_name.required'       => 'Ism majburiy',
            'last_name.required'        => 'Familiya majburiy',
            'hemis_id.unique'           => 'Bu HEMIS ID bilan talaba allaqachon mavjud',
            'student_number.unique'     => 'Bu talaba raqami allaqachon band',
            'jshshir.digits'            => 'JSHSHIR 14 ta raqamdan iborat bo\'lishi kerak',
            'funding_type.required'     => "Moliyalashtirish turini tanlang (Grant yoki Kontrakt)",
        ];
    }

    /**
     * Daraja/ta'lim shakliga qarab kursning yuqori chegarasini tekshiradi
     * (frontenddagi dinamik tanlovni backendda ham mustahkamlash uchun).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('course_year')) {
                return;
            }

            $max = StoreStudentRequest::maxCourseYear($this->input('degree'), $this->input('study_form'));

            if ((int) $this->input('course_year') > $max) {
                $validator->errors()->add(
                    'course_year',
                    "Tanlangan daraja/ta'lim shakli uchun kurs {$max} dan oshmasligi kerak"
                );
            }
        });
    }
}
