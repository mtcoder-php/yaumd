<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProject extends Model
{
    use HasFactory;

    // MUHIM: StaffEducation'da duch kelingan noaniqlikni oldini olish
    // uchun jadval nomi aniq ko'rsatildi (migratsiyaga qarang).
    protected $table = 'staff_projects';

    protected $fillable = ['staff_id', 'title', 'description_uz', 'period', 'role_uz', 'url', 'sort_order'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
