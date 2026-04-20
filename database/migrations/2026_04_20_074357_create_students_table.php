<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id_number', 20)->unique();
            $table->string('hemis_id', 50)->unique()->nullable();
            $table->foreignId('direction_id')->constrained();
            $table->foreignId('group_id')->constrained('student_groups');
            $table->year('academic_year');
            $table->tinyInteger('current_semester')->default(1);
            $table->enum('payment_type', ['grant', 'contract'])->default('contract');
            $table->enum('status', ['active', 'academic_leave', 'expelled', 'graduated'])->default('active');
            $table->date('enrolled_at');
            $table->date('graduated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
