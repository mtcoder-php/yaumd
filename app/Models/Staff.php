<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        // Profil sahifasi ("Umumiy ma'lumot" tabi) uchun qo'shimcha maydonlar.
        'experience_years', 'students_count', 'articles_count', 'projects_count',
        'location', 'research_summary_uz', 'cv_file',
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

    /**
     * Profil sahifasidagi "Ta'lim va malaka" vaqt chizig'i, eng eskisidan
     * (yoki qo'lda belgilangan tartib bo'yicha) ko'rsatiladi.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(StaffEducation::class)->orderBy('sort_order');
    }

    /**
     * Profil sahifasidagi "Ish tajribasi" vaqt chizig'i.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(StaffExperience::class)->orderBy('sort_order');
    }

    /**
     * Profil sahifasidagi "Ijtimoiy tarmoqlar" kartasi.
     */
    public function socialLinks(): HasMany
    {
        return $this->hasMany(StaffSocialLink::class)->orderBy('sort_order');
    }

    /**
     * Profil sahifasidagi "Maqolalar" tabi.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(StaffArticle::class)->orderBy('sort_order');
    }

    /**
     * Profil sahifasidagi "Loyiha va dasturlar" tabi.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(StaffProject::class)->orderBy('sort_order');
    }
}
