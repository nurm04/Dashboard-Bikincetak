<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modifikasi Enum tipe_target pakai Raw SQL (Karena Laravel bawaan sering error kalau modify ENUM)
        DB::statement("ALTER TABLE voucher MODIFY COLUMN tipe_target ENUM('semua_pesanan', 'produk_tertentu', 'sku_tertentu') DEFAULT 'semua_pesanan'");

        Schema::table('voucher', function (Blueprint $table) {
            // 2. Tambah kolom id_produk_target (id_sku_target kan udah ada dari dulu)
            $table->string('id_produk_target')->nullable()->after('tipe_target');

            // 3. Tambah kolom tipe JSON untuk menampung Array Role Customer
            $table->json('role_customer_targets')->nullable()->after('nama_promo');

            // Set Foreign Key untuk produk
            $table->foreign('id_produk_target')->references('id_produk')->on('produk')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('voucher', function (Blueprint $table) {
            $table->dropForeign(['id_produk_target']);
            $table->dropColumn(['id_produk_target', 'role_customer_targets']);
        });

        // Kembalikan Enum seperti semula jika di-rollback
        DB::statement("ALTER TABLE voucher MODIFY COLUMN tipe_target ENUM('semua_pesanan', 'produk_tertentu') DEFAULT 'semua_pesanan'");
    }
};
