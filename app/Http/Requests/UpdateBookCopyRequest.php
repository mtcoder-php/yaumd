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
            'status'          => 'required|in:available,loaned,damaged,lost',
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
