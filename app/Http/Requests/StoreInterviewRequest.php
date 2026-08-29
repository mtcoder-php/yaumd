<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'applicant_id' => 'required|exists:applicants,id',
            'result'       => 'required|in:passed,failed',
            'notes'        => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'applicant_id.required' => 'Abituriyentni tanlang',
            'result.required'       => 'Natijani tanlang',
        ];
    }
}
