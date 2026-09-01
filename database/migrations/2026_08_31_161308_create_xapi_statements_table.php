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
            $table->foreignId('scorm_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('statement_id', 100)->nullable(); // xAPI statement id (klient tomonidan berilishi mumkin)
            $table->string('verb_id', 255)->nullable();      // masalan https://w3id.org/xapi/verbs/completed
            $table->string('verb_display', 255)->nullable();
            $table->string('object_id', 500)->nullable();    // activity IRI
            $table->boolean('result_completion')->nullable();
            $table->boolean('result_success')->nullable();
            $table->decimal('result_score_scaled', 5, 4)->nullable();
            $table->decimal('result_score_raw', 8, 2)->nullable();
            $table->string('result_duration', 50)->nullable(); // ISO 8601 duration
            $table->json('raw'); // to'liq statement (audit/keyingi tahlil uchun)
            $table->timestamps();

            $table->index(['lesson_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xapi_statements');
    }
};
