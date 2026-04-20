<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_uz', 'name_ru', 'name_en',
        'short_name', 'dean_id',
        'description_uz', 'description_ru', 'description_en',
        'image', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function dean(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dean_id');
    }

    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }
}
