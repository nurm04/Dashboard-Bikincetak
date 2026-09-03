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
        Schema::table('produk_sku', function (Blueprint $table) {
            // Tambah harga per halaman/meter. Kasih default 0 biar aman.
            $table->decimal('harga_tambahan_dimensi', 15, 2)->default(0)->after('harga');
        });

        Schema::table('sku_finishing', function (Blueprint $table) {
            // Tambah flag apakah finishing ikut dikali luas meter/jumlah halaman
            $table->boolean('kali_dimensi')->default(false)->after('kali_jumlah_pesan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_sku', function (Blueprint $table) {
            $table->dropColumn('harga_tambahan_dimensi');
        });

        Schema::table('sku_finishing', function (Blueprint $table) {
            $table->dropColumn('kali_dimensi');
        });
    }
};
