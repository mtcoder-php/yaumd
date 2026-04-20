<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'student_id_number', 'hemis_id',
        'direction_id', 'group_id', 'academic_year',
        'current_semester', 'payment_type', 'status',
        'enrolled_at', 'graduated_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at'  => 'date',
            'graduated_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }
}
