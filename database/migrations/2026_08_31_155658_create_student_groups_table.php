<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('direction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            // Guruh rahbari ("Tutor" roli) — bitta tutorga bir nechta guruh
            // biriktirilishi mumkin (shu sababli bu yerda oddiy nullable FK
            // yetarli, alohida pivot jadval kerak emas). Tutorning KPI'si
            // (oylik kontrakt to'lovi %) TutorKpiService orqali hisoblanadi.
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('hemis_id', 50)->unique()->nullable();
            $table->string('name', 50);                    // MT-1-24
            $table->enum('degree', ['bachelor', 'master'])->default('bachelor');
            $table->enum('study_form', ['full_time', 'evening', 'distance'])->default('full_time');
            $table->tinyInteger('course_year')->default(1); // 1,2,3,4
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_groups');
    }
};
