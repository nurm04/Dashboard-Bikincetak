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
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('id_pembelian');
            $table->string('id_bahan_baku');
            $table->integer('jumlah');
            $table->float('harga_satuan');
            $table->float('subtotal');
            $table->foreign('id_pembelian')->references('id_pembelian')->on('pembelian_bahan')->onDelete('cascade');
            $table->foreign('id_bahan_baku')->references('id_bahan_baku')->on('bahan_baku')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelians');
    }
};
