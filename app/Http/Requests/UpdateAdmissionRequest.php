<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('phone')) {
            $mergeData['phone'] = preg_replace('/[^\d+]/', '', $this->phone);
        }

        if ($this->has('extra_phone')) {
            $mergeData['extra_phone'] = $this->extra_phone
                ? preg_replace('/[^\d+]/', '', $this->extra_phone)
                : null;
        }

        if ($this->has('passport_series')) {
            $mergeData['passport_series'] = Str::upper($this->passport_series);
        }

        if ($this->has('first_name'))  $mergeData['first_name']  = Str::upper($this->first_name);
        if ($this->has('last_name'))   $mergeData['last_name']   = Str::upper($this->last_name);
        if ($this->has('middle_name')) $mergeData['middle_name'] = Str::upper($this->middle_name);

        $this->merge($mergeData);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'education_type'  => 'required|in:bachelor,master,transfer,second',
            'direction_id'    => 'required|exists:directions,id',
            'study_form'      => 'required|in:full_time,evening,distance',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'middle_name'     => 'required|string|max:100',
            'birth_day'       => 'required|integer|min:1|max:31',
            'birth_month'     => 'required|integer|min:1|max:12',
            'birth_year'      => 'required|integer|min:1950|max:' . (date('Y') - 14),
            'gender'          => 'required|in:male,female',
            'passport_series' => 'required|string|max:9|unique:applicants,passport_series,' . $id,
            'phone'           => 'required|string|max:15',
            'extra_phone'     => 'nullable|string|max:15',
            'region_id'       => 'required|exists:regions,id',
            'district_id'     => 'required|exists:districts,id',
            'address'         => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'passport_series.unique' => 'Bu pasport seriyasi bilan boshqa ariza mavjud!',
        ];
    }
}
