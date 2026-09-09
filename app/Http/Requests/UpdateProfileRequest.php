<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'  => 'required|string|max:255',
            // Frontend ("+998 (XX) XXX-XX-XX" shabloni, Index.vue'ga
            // qarang) har doim yo bo'sh, yo to'liq "998" + 9 ta raqam
            // (jami 12 ta) yuboradi — shuning uchun bu yerda ham aynan
            // shu formatga qat'iy mos kelishi talab qilinadi, aks holda
            // yarim to'ldirilgan raqam bazaga yozilib qolishi mumkin edi.
            'phone'      => 'nullable|string|regex:/^998\d{9}$/|unique:users,phone,' . $this->user()->id,
            'address'    => 'nullable|string|max:1000',
            'birth_date' => 'nullable|date|before:today',
            'gender'     => 'nullable|in:male,female',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'To\'liq ismni kiriting',
            'phone.regex'        => 'Telefon raqamni to\'liq kiriting',
            'phone.unique'       => 'Bu telefon raqam boshqa hisobda band',
            'birth_date.before'  => 'Tug\'ilgan sana noto\'g\'ri',
            'gender.in'          => 'Jinsni to\'g\'ri tanlang',
        ];
    }
}
