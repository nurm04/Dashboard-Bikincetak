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
        Schema::create('komposisi', function (Blueprint $table) {
            $table->id();
            $table->string('id_sku');
            $table->string('id_bahan_baku');
            $table->string('jumlah_pakai');
            $table->float('hpp');
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
            $table->foreign('id_bahan_baku')->references('id_bahan_baku')->on('bahan_baku')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komposisi');
    }
};
