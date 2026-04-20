<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name_uz', 'full_name_ru', 'full_name_en',
        'position_uz', 'position_ru', 'position_en',
        'department_uz', 'department_ru', 'department_en',
        'bio_uz', 'bio_ru', 'bio_en',
        'photo', 'email', 'phone', 'reception_hours',
        'type', 'order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
