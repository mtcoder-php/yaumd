<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupStudent extends Model
{
    protected $fillable = ['student_group_id', 'student_id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
