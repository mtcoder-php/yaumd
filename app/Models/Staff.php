<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'full_name_uz', 'full_name_ru', 'full_name_en',
        'position_uz', 'position_ru', 'position_en',
        'department_uz', 'department_ru', 'department_en',
        'bio_uz', 'bio_ru', 'bio_en',
        'photo', 'email', 'phone', 'reception_hours',
        'type', 'faculty_id', 'department_id', 'degree', 'research_tags',
        'order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'research_tags' => 'array',
        ];
    }

    /**
     * "Professor & o'qituvchilar" sahifasidagi Fakultet filtri uchun —
     * faqat type=teacher yozuvlarda to'ldiriladi.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Kafedra filtri uchun — faqat type=teacher yozuvlarda to'ldiriladi.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
