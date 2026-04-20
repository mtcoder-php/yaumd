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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('application_number', 20)->unique();
            $table->string('full_name', 255);
            $table->string('passport_series', 9);
            $table->string('jshshir', 14);
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('nationality', 100)->nullable();
            $table->string('phone', 15);
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('education_type', ['bachelor', 'master', 'transfer', 'second'])->default('bachelor');
            $table->foreignId('direction_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['new', 'accepted', 'interview', 'tested', 'contracted', 'enrolled', 'rejected'])->default('new');
            $table->timestamp('interview_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
