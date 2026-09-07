<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 'library_access' jadvali dastlab 'payments' jadvaliga bog'langan
     * 'payment_id' ustuniga ega edi. Endi pullik elektron kitob xaridi
     * uchun alohida 'book_purchases' jadvali yaratilgani sababli, shu
     * ustun o'sha yangi jadvalga ishora qiladigan 'purchase_id' bilan
     * almashtiriladi. 'library_access' jadvali hali hech qanday kodda
     * ishlatilmagan (ma'lumot yo'q) edi, shuning uchun bu xavfsiz o'zgarish.
     */
    public function up(): void
    {
        Schema::table('library_access', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
            $table->foreignId('purchase_id')->nullable()->after('book_id')
                ->constrained('book_purchases')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_access', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_id');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
