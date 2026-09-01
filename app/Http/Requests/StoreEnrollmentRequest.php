<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Yoki bitta talaba (student_id), yoki butun guruh (group_id) —
            // ikkalasidan kamida bittasi kerak.
            'student_id' => 'required_without:group_id|nullable|integer|exists:students,id',
            'group_id'   => 'required_without:student_id|nullable|integer|exists:student_groups,id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required_without' => "Talaba yoki guruhni tanlang",
            'group_id.required_without'   => "Talaba yoki guruhni tanlang",
            'student_id.exists'           => "Tanlangan talaba topilmadi",
            'group_id.exists'             => "Tanlangan guruh topilmadi",
        ];
    }
}
