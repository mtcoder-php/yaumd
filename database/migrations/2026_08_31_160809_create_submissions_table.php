<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('attempt')->default(1);
            $table->longText('text_answer')->nullable();
            $table->json('files')->nullable();
            $table->enum('status', ['submitted', 'reviewing', 'reviewed', 'returned'])->default('submitted');
            $table->integer('score')->nullable();
            $table->boolean('is_late')->default(false);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
