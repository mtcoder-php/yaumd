<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_uz'     => 'required|string|max:255',
            'title_ru'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:video,pdf,text,scorm',
            'order'        => 'nullable|integer|min:0',
            'duration'     => 'nullable|integer|min:0',
            'is_free'      => 'boolean',
            'is_published' => 'boolean',
            'content'      => 'required_if:type,text|nullable|string',
            'video_source' => 'required_if:type,video|nullable|in:upload,youtube,vimeo',
            'video_url'    => 'nullable|url|max:500',
            'video_file'   => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:512000',
            // SCORM 1.2 / SCORM 2004 / xAPI (Tin Can) — hammasi ZIP fayl
            // sifatida yuklanadi, versiya (scorm12/scorm2004/xapi) ichidagi
            // imsmanifest.xml yoki tincan.xml'dan avtomatik aniqlanadi.
            'scorm_file'   => 'required_if:type,scorm|nullable|file|mimes:zip|max:512000',
            'files'        => 'nullable|array',
            'files.*'      => 'file|max:51200',
        ];
    }

    public function messages(): array
    {
        return [
            'title_uz.required'     => 'Dars nomi majburiy',
            'type.required'         => 'Dars turini tanlang',
            'content.required_if'   => "Matn turidagi dars uchun matn kiritish majburiy",
            'video_source.required_if' => "Video manbasini tanlang",
            'video_url.url'         => "To'g'ri havola kiriting (https://...)",
            'video_file.mimetypes'  => "Video fayl formati noto'g'ri (mp4, webm yoki ogg bo'lishi kerak)",
            'video_file.max'        => "Video hajmi 500 MB dan oshmasligi kerak",
            'scorm_file.required_if' => "SCORM/xAPI paket faylini (.zip) yuklang",
            'scorm_file.mimes'      => "Fayl .zip formatida bo'lishi kerak",
            'scorm_file.max'        => "Paket hajmi 500 MB dan oshmasligi kerak",
            'files.*.max'           => "Har bir fayl hajmi 50 MB dan oshmasligi kerak",
        ];
    }
}
