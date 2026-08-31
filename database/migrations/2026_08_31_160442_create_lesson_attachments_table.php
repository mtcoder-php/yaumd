<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->enum('type', ['pdf', 'docx', 'pptx', 'xlsx', 'other'])->default('pdf');
            $table->boolean('is_downloadable')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_attachments');
    }
};
