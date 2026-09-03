<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'     => 'required|exists:library_categories,id',
            'isbn'            => 'nullable|string|max:20|unique:library_books,isbn',
            'title'           => 'required|string|max:500',
            'author'          => 'required|string|max:255',
            'publisher'       => 'nullable|string|max:255',
            'published_year'  => 'nullable|digits:4|integer|min:1500|max:' . (date('Y') + 1),
            'language'        => 'required|string|max:50',
            'description'     => 'nullable|string',
            'cover_image'     => 'nullable|image|max:4096',
            'page_count'      => 'nullable|integer|min:1',
            'shelf_location'  => 'nullable|string|max:100',
            'is_active'       => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'    => "Kategoriyani tanlang",
            'category_id.exists'      => 'Tanlangan kategoriya topilmadi',
            'isbn.unique'             => 'Bu ISBN raqami bilan kitob allaqachon mavjud',
            'title.required'          => 'Kitob nomi majburiy',
            'author.required'         => 'Muallif majburiy',
            'published_year.digits'   => "Nashr yili 4 ta raqamdan iborat bo'lishi kerak",
            'cover_image.image'       => "Muqova rasm fayli bo'lishi kerak",
            'cover_image.max'         => "Muqova hajmi 4 MB dan oshmasligi kerak",
        ];
    }
}
