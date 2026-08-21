<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question'       => 'required|string',
            'option_a'       => 'required|string|max:500',
            'option_b'       => 'required|string|max:500',
            'option_c'       => 'required|string|max:500',
            'option_d'       => 'required|string|max:500',
            'correct_answer' => 'required|in:a,b,c,d',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required'     => 'Fanni tanlang',
            'subject_id.exists'       => 'Tanlangan fan mavjud emas',
            'language.required'       => 'Tilni tanlang',
            'language.in'             => "Til faqat 'uz' yoki 'ru' bo'lishi mumkin",
            'question.required'       => 'Savol matnini kiriting',
            'option_a.required'       => 'A variantini kiriting',
            'option_b.required'       => 'B variantini kiriting',
            'option_c.required'       => 'C variantini kiriting',
            'option_d.required'       => 'D variantini kiriting',
            'correct_answer.required' => "To'g'ri javobni tanlang",
            'correct_answer.in'       => "To'g'ri javob a, b, c yoki d bo'lishi kerak",
        ];
    }
}
