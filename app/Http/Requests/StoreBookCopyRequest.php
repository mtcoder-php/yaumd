<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Inventar raqamlari endi qo'lda kiritilmaydi — nechta nusxa
            // kerakligi kiritiladi, tizim BookCopy::nextInventoryCodes()
            // orqali shunchalik sonda ketma-ket raqam avtomatik yaratadi
            // (qarang: BookCopyController::store()). "loaned" holati bu
            // yerda ataylab yo'q — yangi qo'shilayotgan nusxa hech qachon
            // to'g'ridan-to'g'ri "olingan" holatda yaratilmasligi kerak,
            // bu FAQAT kitob berish oqimi orqali (LibraryLoanController)
            // sodir bo'lishi mumkin.
            'quantity'        => 'required|integer|min:1|max:100',
            'status'          => 'required|in:available,damaged,lost',
            'condition_notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => "Nusxalar sonini kiriting",
            'quantity.integer'  => "Nusxalar soni butun son bo'lishi kerak",
            'quantity.min'      => 'Kamida 1 ta nusxa kiritilishi kerak',
            'quantity.max'      => "Bir martada ko'pi bilan 100 ta nusxa qo'shish mumkin",
            'status.required'   => 'Nusxa holatini tanlang',
        ];
    }
}
