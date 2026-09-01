<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'applicant_id', 'academic_year_id', 'direction_id', 'department_id',
        'hemis_id', 'student_number', 'first_name', 'last_name', 'middle_name',
        'passport_series', 'jshshir', 'phone', 'email',
        'birth_day', 'birth_month', 'birth_year', 'gender',
        'degree', 'study_form', 'course_year', 'status', 'funding_type',
        'photo', 'address', 'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Abituriyentlar oqimidan o'tmagan (to'g'ridan-to'g'ri kiritilgan/import
     * qilingan) talabaning o'ziga tegishli kontrakti — bunday holatda
     * kontrakt applicant_id emas, student_id orqali bog'lanadi.
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
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

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroup::class, 'group_students')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'user_id', 'user_id');
    }
}
