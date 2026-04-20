<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title_uz', 'title_ru', 'title_en',
        'description_uz', 'description_ru', 'description_en',
        'image', 'link',
        'button_text_uz', 'button_text_ru', 'button_text_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
