<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->integer('watch_time')->default(0);   // sekund (video uchun)
            $table->integer('duration')->default(0);     // umumiy davomiylik
            $table->decimal('progress', 5, 2)->default(0); // 0-100%
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['lesson_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
