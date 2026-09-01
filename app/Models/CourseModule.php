<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    protected $fillable = [
        'course_id', 'title_uz', 'title_ru',
        'description', 'order', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        // Diqqat: `lessons` jadvalidagi haqiqiy ustun nomi `module_id`
        // (migratsiyaga qarang), lekin hasMany() standart bo'yicha
        // "course_module_id" deb taxmin qiladi (chunki bu yerdagi model
        // "CourseModule" deb nomlangan) — shu nomuvofiqlik "Unknown column
        // lessons.course_module_id" xatosiga sabab bo'lgan. Shuning uchun
        // to'g'ri ustun nomi qo'lda ko'rsatilgan.
        return $this->hasMany(Lesson::class, 'module_id')->orderBy('order');
    }
}
