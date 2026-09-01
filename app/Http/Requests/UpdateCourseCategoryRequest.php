<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable', 'exists:course_categories,id',
                Rule::notIn([$this->route('id')]),
            ],
            'name_uz'   => 'required|string|max:100',
            'name_ru'   => 'nullable|string|max:100',
            'name_en'   => 'nullable|string|max:100',
            'icon'      => 'nullable|string|max:100',
            'color'     => 'nullable|string|max:20',
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name_uz.required' => "Kategoriya nomi majburiy",
            'parent_id.exists' => 'Tanlangan ota-kategoriya topilmadi',
            'parent_id.not_in' => "Kategoriya o'zini ota-kategoriya qilib bo'lmaydi",
        ];
    }
}
