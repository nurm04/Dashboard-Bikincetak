<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk_varian', function (Blueprint $table) {
            // Tambahkan kolom penanda jenis varian
            if (!Schema::hasColumn('produk_varian', 'jenis_varian')) {
                $table->enum('jenis_varian', ['utama', 'tambahan'])->default('utama')->after('id_varian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produk_varian', function (Blueprint $table) {
            if (Schema::hasColumn('produk_varian', 'jenis_varian')) {
                $table->dropColumn('jenis_varian');
            }
        });
    }
};
