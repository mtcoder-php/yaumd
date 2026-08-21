<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectionSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'direction_id'       => 'required|exists:directions,id',
            'subject_id'         => 'required|exists:subjects,id',
            'block_type'         => 'required|in:mandatory,specialty_1,specialty_2',
            'questions_count'    => 'required|integer|min:1|max:100',
            'score_per_question' => 'required|numeric|min:0.1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'direction_id.required'       => "Yo'nalishni tanlang",
            'subject_id.required'         => 'Fanni tanlang',
            'block_type.required'         => 'Blok turini tanlang',
            'block_type.in'               => "Blok turi noto'g'ri",
            'questions_count.required'    => 'Savollar sonini kiriting',
            'questions_count.min'         => 'Kamida 1 ta savol bo\'lishi kerak',
            'questions_count.max'         => 'Ko\'pi bilan 100 ta savol bo\'lishi mumkin',
            'score_per_question.required' => 'Ball miqdorini kiriting',
            'score_per_question.min'      => 'Ball 0.1 dan kam bo\'lmasin',
            'score_per_question.max'      => 'Ball 10 dan ko\'p bo\'lmasin',
        ];
    }
}
