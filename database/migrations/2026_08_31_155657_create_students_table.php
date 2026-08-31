<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('direction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hemis_id', 50)->unique()->nullable();
            $table->string('student_number', 20)->unique()->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('passport_series', 20)->nullable();
            $table->string('jshshir', 14)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->tinyInteger('birth_day')->nullable();
            $table->tinyInteger('birth_month')->nullable();
            $table->smallInteger('birth_year')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('degree', ['bachelor', 'master'])->default('bachelor');
            $table->enum('study_form', ['full_time', 'evening', 'distance'])->default('full_time');
            $table->tinyInteger('course_year')->default(1);
            $table->enum('status', ['active', 'academic_leave', 'expelled', 'graduated', 'transferred'])->default('active');
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
