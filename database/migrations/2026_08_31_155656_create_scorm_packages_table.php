<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorm_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->enum('version', ['scorm12', 'scorm2004', 'xapi'])->default('scorm12');
            $table->string('path', 500);           // ZIP fayl joyi
            $table->string('launch_url', 500);     // index fayl
            $table->string('identifier', 255)->nullable(); // manifest ID
            $table->json('manifest')->nullable();  // imsmanifest.xml
            $table->bigInteger('file_size')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_packages');
    }
};
