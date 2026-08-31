<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xapi_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('statement_id', 100)->unique(); // UUID
            $table->string('verb', 255);                   // experienced, completed, passed, failed
            $table->string('object_id', 500);              // activity IRI
            $table->string('object_type', 100)->nullable();
            $table->json('actor')->nullable();
            $table->json('result')->nullable();
            $table->json('context')->nullable();
            $table->json('raw')->nullable();               // to'liq statement
            $table->timestamp('stored_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xapi_statements');
    }
};
