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
        Schema::table('pesanan_item_produksi', function (Blueprint $table) {
            $table->enum('kategori_produksi', ['digital', 'offset', 'indoor', 'outdoor', 'sablon', 'desain'])
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_item_produksi', function (Blueprint $table) {
            $table->enum('kategori_produksi', ['digital', 'offset', 'indoor', 'outdoor', 'sablon'])
                  ->nullable()
                  ->change();
        });
    }
};
