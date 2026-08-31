<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title_uz', 255);
            $table->string('title_ru', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('passing_score')->default(70);  // o'tish bali %
            $table->integer('time_limit')->default(0);      // daqiqa, 0=cheksiz
            $table->integer('max_attempts')->default(3);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('show_result')->default(true);  // natijani ko'rsatish
            $table->boolean('show_answers')->default(false); // to'g'ri javoblarni ko'rsatish
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
