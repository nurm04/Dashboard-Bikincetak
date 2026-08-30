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
        Schema::table('pesanan_item', function (Blueprint $table) {
            // 1. Putus dulu relasi foreign key yang lama
            $table->dropForeign(['id_sku']);

            // 2. Ubah kolom id_sku jadi boleh kosong (nullable)
            $table->string('id_sku')->nullable()->change();

            // 3. Pasang lagi relasinya dengan tambahan onDelete('set null')
            $table->foreign('id_sku')
                  ->references('id_sku')
                  ->on('produk_sku')
                  ->onDelete('set null'); // <--- INI KUNCINYA
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_item', function (Blueprint $table) {
            // Rollback jika di-reverse
            $table->dropForeign(['id_sku']);

            // Kembalikan ke NOT NULL
            $table->string('id_sku')->nullable(false)->change();

            // Kembalikan ke relasi default (restrict)
            $table->foreign('id_sku')
                  ->references('id_sku')
                  ->on('produk_sku');
        });
    }
};
