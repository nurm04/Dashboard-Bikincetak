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
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('id_sku');
            $table->string('id_pesan');
            $table->string('jumlah');
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
            $table->foreign('id_pesan')->references('id_pesan')->on('pesan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
