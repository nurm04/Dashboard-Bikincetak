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
        Schema::create('pesanan_item', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesan');
            $table->string('id_sku');
            $table->string('nama_produk_snapshot');
            $table->string('jumlah');
            $table->string('harga_satuan_snapshot');
            $table->text('file_desain')->nullable();
            $table->text('catatan')->nullable();
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku');
            $table->foreign('id_pesan')->references('id_pesan')->on('pesan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_item');
    }
};
