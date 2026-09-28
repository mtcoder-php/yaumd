<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_code'  => 'required|string|max:50|unique:book_copies,inventory_code,' . $this->route('copyId'),
            // 'reserved' shu ro'yxatda bo'lishi SHART — aks holda nusxa
            // band qilingan holatda ekan, kutubxonachi shu nusxaning
            // 'condition_notes' kabi boshqa maydonini tahrirlab saqlashga
            // urinsagina ham (status dropdown'ga tegmasa ham) validatsiya
            // "status noto'g'ri qiymat" xatosi bilan muvaffaqiyatsiz bo'lib
            // qolar edi (BookCopyController::update() qo'lda 'reserved'ga
            // O'TKAZISHNING o'zini alohida taqiqlaydi, bu yerda faqat
            // MAVJUD 'reserved' qiymatini qabul qilish uchun).
            'status'          => 'required|in:available,loaned,reserved,damaged,lost',
            'condition_notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'inventory_code.required' => "Inventar raqami majburiy",
            'inventory_code.unique'   => 'Bu inventar raqami allaqachon band',
            'status.required'         => 'Nusxa holatini tanlang',
        ];
    }
}
