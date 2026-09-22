<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffExperience extends Model
{
    use HasFactory;

    // MUHIM: StaffEducation'dagi kabi noaniqlikni oldini olish uchun
    // jadval nomi aniq ko'rsatildi (migratsiyaga qarang: staff_experiences).
    protected $table = 'staff_experiences';

    protected $fillable = ['staff_id', 'period', 'title', 'subtitle', 'sort_order'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
