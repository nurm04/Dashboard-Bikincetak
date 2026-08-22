<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk_sku', function (Blueprint $table) {
            // Cek dulu biar nggak error kalau ternyata kolomnya udah pernah lu bikin manual
            if (!Schema::hasColumn('produk_sku', 'gambar')) {
                $table->text('gambar')->nullable()->after('nama_sku');
            }

            // 👇 TAMBAHAN: Kolom satuan 👇
            if (!Schema::hasColumn('produk_sku', 'satuan')) {
                $table->string('satuan', 50)->default('pcs')->after('minimum_pesan')->comment('Satuan produk (pcs, pack, rim, box, dll)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produk_sku', function (Blueprint $table) {
            if (Schema::hasColumn('produk_sku', 'gambar')) {
                $table->dropColumn('gambar');
            }

            // 👇 Rollback kolom satuan 👇
            if (Schema::hasColumn('produk_sku', 'satuan')) {
                $table->dropColumn('satuan');
            }
        });
    }
};
