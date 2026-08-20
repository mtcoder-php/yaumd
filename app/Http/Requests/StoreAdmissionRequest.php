<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validatsiyadan oldin ma'lumotlarni tayyorlash (tozalash)
     */
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        // Telefon raqamlarini tozalash
        if ($this->has('phone')) {
            $mergeData['phone'] = preg_replace('/[^\d+]/', '', $this->phone);
        }

        if ($this->has('extra_phone')) {
            $mergeData['extra_phone'] = $this->extra_phone
                ? preg_replace('/[^\d+]/', '', $this->extra_phone)
                : null;
        }

        // Pasport seriyasini uppercase qilish
        if ($this->has('passport_series')) {
            $mergeData['passport_series'] = Str::upper(mb_strtoupper($this->passport_series));
        }

        $this->merge($mergeData);
    }

    /**
     * Validatsiya qoidalari
     */
    public function rules(): array
    {
        $rules = [
            'education_type'  => 'required|in:bachelor,master,transfer,second',
            'direction_id'    => 'required|exists:directions,id',
            'study_form'      => 'required|in:full_time,evening,distance',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'middle_name'     => 'required|string|max:100',
            'birth_year'      => 'required|integer|min:1950|max:' . (date('Y') - 14),
            'birth_month'     => 'required|integer|min:1|max:12',
            'birth_day'       => 'required|integer|min:1|max:31',
            'gender'          => 'required|in:male,female',
            'passport_series' => 'required|string|max:9|unique:applicants,passport_series',
            'region_id'       => 'required|exists:regions,id',
            'district_id'     => 'required|exists:districts,id',
            'phone'           => 'required|string|max:15',
            'extra_phone'     => 'nullable|string|max:15',
        ];

        // Magistratura uchun alohida hujjat talablari
        if ($this->input('education_type') === 'master') {
            $rules['passport_file']         = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules['diploma_file']          = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules['diploma_appendix_file'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        return $rules;
    }

    /**
     * Custom xato xabarlari
     */
    public function messages(): array
    {
        return [
            'passport_series.unique' => 'Ushbu pasport seriyasi bo\'yicha ariza allaqachon topshirilgan!',
        ];
    }
}
