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
    Schema::create('keranjang', function (Blueprint $table) {
        $table->id();
        $table->string('id_customer');
        $table->string('id_sku');
        $table->integer('jumlah');
        $table->text('catatan')->nullable();
        $table->string('file_desain')->nullable();
        $table->timestamps();

        // Foreign keys
        $table->foreign('id_customer')->references('id_customer')->on('customer')->onDelete('cascade');
        $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
