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
            // Ditambahin setelah minimum_pesan biar rapi posisinya
            $table->integer('kelipatan_pesan')->default(1)->after('minimum_pesan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_sku', function (Blueprint $table) {
            $table->dropColumn('kelipatan_pesan');
        });
    }
};
