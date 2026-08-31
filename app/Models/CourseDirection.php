<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseDirection extends Model
{
    protected $fillable = ['course_id', 'direction_id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }
}
