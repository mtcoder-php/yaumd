<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $fillable = [
        'category_id', 'name',
        'duration_minutes', 'total_questions',
        'pass_score', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'category_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TestSession::class);
    }
}
