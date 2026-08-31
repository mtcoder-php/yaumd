<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_directions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('direction_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_id', 'direction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_directions');
    }
};
