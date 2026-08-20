<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Ariza
            $table->string('application_number', 20)->unique();
            $table->enum('education_type', ['bachelor', 'master', 'transfer', 'second'])->default('bachelor');
            $table->enum('study_form', ['full_time', 'evening', 'distance'])->default('full_time');
            $table->foreignId('direction_id')->nullable()->constrained()->nullOnDelete();

            // Shaxsiy ma'lumotlar
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('middle_name', 100);
            $table->tinyInteger('birth_day');
            $table->tinyInteger('birth_month');
            $table->smallInteger('birth_year');
            $table->enum('gender', ['male', 'female']);
            $table->string('nationality', 100)->nullable();

            // Hujjat va aloqa
            $table->string('passport_series', 9);
            $table->string('jshshir', 14)->nullable();
            $table->string('passport_file', 500)->nullable();
            $table->string('phone', 20);
            $table->string('extra_phone', 20)->nullable();
            $table->string('email', 255)->nullable();

            // Magistr uchun fayllar
            $table->string('diploma_file', 500)->nullable();
            $table->string('diploma_appendix_file', 500)->nullable();

            // Manzil
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->text('address')->nullable();

            // Qo'shimcha
            $table->string('previous_diploma', 255)->nullable();
            $table->string('previous_edu_place', 255)->nullable();

            // Status
            $table->enum('status', ['new', 'accepted', 'interview', 'tested', 'contracted', 'enrolled', 'rejected'])->default('new');
            $table->timestamp('interview_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
