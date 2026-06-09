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
    Schema::create('keranjang_finishing', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_keranjang')->constrained('keranjang')->onDelete('cascade');
        $table->unsignedBigInteger('id_sku_finishing'); // Sesuaikan dengan tipe primary key tabel sku_finishing
        $table->timestamps();

        // Asumsi primary key di tabel sku_finishing adalah 'id'
        $table->foreign('id_sku_finishing')->references('id')->on('sku_finishing')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keranjang_finishing');
    }
};
