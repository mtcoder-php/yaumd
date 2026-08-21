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
        Schema::table('test_questions', function (Blueprint $table) {
            // test_id o'rniga subject_id
            $table->dropForeign(['test_id']);
            $table->dropColumn('test_id');

            $table->foreignId('subject_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('language', ['uz', 'ru'])
                ->default('uz')
                ->after('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_questions', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['subject_id', 'language']);
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
        });
    }
};
