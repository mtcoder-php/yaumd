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
     * 'payment_id' ustuniga ega edi. Endi pullik narsalar (kitob, kurs...)
     * uchun umumiy 'payment_orders' jadvali yaratilgani sababli, shu ustun
     * o'sha yangi jadvalga ishora qiladigan 'payment_order_id' bilan
     * almashtiriladi. 'library_access' jadvali hali hech qanday kodda
     * ishlatilmagan (ma'lumot yo'q) edi, shuning uchun bu xavfsiz o'zgarish.
     */
    public function up(): void
    {
        Schema::table('library_access', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
            $table->foreignId('payment_order_id')->nullable()->after('book_id')
                ->constrained('payment_orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_access', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_order_id');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
