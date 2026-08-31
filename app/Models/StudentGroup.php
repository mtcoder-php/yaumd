<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentGroup extends Model
{
    protected $fillable = [
        'academic_year_id', 'direction_id', 'department_id',
        'head_teacher_id', 'hemis_id', 'name',
        'degree', 'study_form', 'course_year', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_students');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_groups');
    }
}
