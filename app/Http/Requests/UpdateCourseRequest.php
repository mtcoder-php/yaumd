<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'      => 'nullable|exists:course_categories,id',
            'title_uz'         => 'required|string|max:255',
            'title_ru'         => 'nullable|string|max:255',
            'title_en'         => 'nullable|string|max:255',
            'description_uz'   => 'nullable|string',
            'description_ru'   => 'nullable|string',
            'description_en'   => 'nullable|string',
            'what_you_learn'   => 'nullable|string',
            'requirements'     => 'nullable|string',
            'thumbnail'        => 'nullable|image|max:4096',
            'type'             => 'required|in:open,free,paid,students_only',
            'level'            => 'required|in:beginner,intermediate,advanced,expert',
            'language'         => 'required|in:uz,ru,en',
            'degree'           => 'required|in:bachelor,master,both',
            'price'            => 'required|numeric|min:0',
            'discount_price'   => 'nullable|numeric|min:0|lt:price',
            'duration_hours'   => 'required|integer|min:0',
            'has_certificate'  => 'boolean',
            'is_sequential'    => 'boolean',
            'status'           => 'required|in:draft,published,archived',
            'instructor_ids'   => 'nullable|array',
            'instructor_ids.*' => 'exists:users,id',
            'direction_ids'    => 'nullable|array',
            'direction_ids.*'  => 'exists:directions,id',
            'group_ids'        => 'nullable|array',
            'group_ids.*'      => 'exists:student_groups,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title_uz.required'       => "Kurs nomi (o'zbek tilida) majburiy",
            'thumbnail.image'         => 'Muqova rasm fayli bo\'lishi kerak',
            'thumbnail.max'           => "Muqova hajmi 4 MB dan oshmasligi kerak",
            'type.required'           => 'Kurs turini tanlang',
            'level.required'          => 'Daraja (level) ni tanlang',
            'price.required'          => 'Narxni kiriting (bepul bo\'lsa 0)',
            'discount_price.lt'       => "Chegirmali narx asosiy narxdan kichik bo'lishi kerak",
            'duration_hours.required' => 'Davomiylikni (soatlarda) kiriting',
            'status.required'        => 'Holatni tanlang',
        ];
    }
}
