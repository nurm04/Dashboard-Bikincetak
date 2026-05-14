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
        Schema::create('sku_finishing', function (Blueprint $table) {
            $table->id();
            $table->string('id_sku');
            $table->string('id_pilihan_finishing');
            $table->integer('minimum_pesan')->default(1);
            $table->float('harga_tambahan');
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
            $table->foreign('id_pilihan_finishing')->references('id_pilihan_finishing')->on('pilihan_finishing')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_finishings');
    }
};
