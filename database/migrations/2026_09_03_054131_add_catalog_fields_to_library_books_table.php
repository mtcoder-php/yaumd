<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fizik kutubxona katalogi uchun qo'shimcha maydonlar: sahifalar soni,
     * javon/qator manzili va kim tomonidan bazaga kiritilgani. Mavjud
     * raqamli (elektron) maydonlar — file_path, access_type, price,
     * download_count, view_count — o'zgarishsiz qoladi, chunki bitta kitob
     * yozuvi ham raqamli fayl, ham fizik nusxalarga ega bo'lishi mumkin.
     */
    public function up(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->unsignedInteger('page_count')->nullable()->after('description');
            $table->string('shelf_location', 100)->nullable()->after('page_count');
            $table->foreignId('added_by')->nullable()->after('shelf_location')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('added_by');
            $table->dropColumn(['page_count', 'shelf_location']);
        });
    }
};
