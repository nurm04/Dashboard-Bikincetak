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
        Schema::create('sku_detail_pilihan', function (Blueprint $table) {
            $table->id();
            $table->string('id_sku');
            $table->string('id_pilihan');
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
            $table->foreign('id_pilihan')->references('id_pilihan')->on('pilihan_varian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_detail_pilihan');
    }
};
