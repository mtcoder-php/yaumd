<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDirectionSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'block_type'         => 'required|in:mandatory,specialty_1,specialty_2',
            'questions_count'    => 'required|integer|min:1|max:100',
            'score_per_question' => 'required|numeric|min:0.1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'block_type.required'         => 'Blok turini tanlang',
            'questions_count.required'    => 'Savollar sonini kiriting',
            'score_per_question.required' => 'Ball miqdorini kiriting',
        ];
    }
}
