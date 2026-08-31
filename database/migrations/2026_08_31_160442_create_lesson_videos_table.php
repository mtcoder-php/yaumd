<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->enum('source', ['upload', 'youtube', 'vimeo'])->default('upload');
            $table->string('url', 500)->nullable();           // YouTube/Vimeo link
            $table->string('path_360', 500)->nullable();      // 360p
            $table->string('path_720', 500)->nullable();      // 720p
            $table->string('path_1080', 500)->nullable();     // 1080p
            $table->string('path_2k', 500)->nullable();       // 2K
            $table->string('path_4k', 500)->nullable();       // 4K
            $table->string('path_8k', 500)->nullable();       // 8K
            $table->string('thumbnail', 500)->nullable();
            $table->integer('duration')->default(0);          // sekund
            $table->bigInteger('file_size')->default(0);
            $table->boolean('is_processed')->default(false);  // transcoding
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_videos');
    }
};
