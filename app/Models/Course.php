<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'created_by',
        'title_uz', 'title_ru', 'title_en',
        'description_uz', 'description_ru', 'description_en',
        'what_you_learn', 'requirements',
        'thumbnail', 'promo_video',
        'type', 'scorm_type', 'level', 'language', 'degree',
        'price', 'discount_price', 'duration_hours',
        'has_certificate', 'is_sequential',
        'rating_avg', 'rating_count', 'students_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'what_you_learn'  => 'array',
            'requirements'    => 'array',
            'has_certificate' => 'boolean',
            'is_sequential'   => 'boolean',
            'price'           => 'decimal:2',
            'discount_price'  => 'decimal:2',
            'rating_avg'      => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructors')->withPivot('role');
    }

    public function directions(): BelongsToMany
    {
        return $this->belongsToMany(Direction::class, 'course_directions');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroup::class, 'course_groups');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(CourseRating::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
