<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestion extends Model
{
    protected $fillable = [
        'test_id', 'question',
        'option_a', 'option_b', 'option_c', 'option_d',
        'correct_answer', 'order', 'is_active',
    ];

    protected $hidden = ['correct_answer'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
