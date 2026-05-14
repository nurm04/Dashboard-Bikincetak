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
        Schema::create('pesanan_item_finishings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pesanan_item')->constrained('pesanan_item')->onDelete('cascade');
            $table->string('id_sku_finishing');
            $table->string('nama_finishing_snapshot');
            $table->float('harga_finishing_snapshot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_item_finishing');
    }
};
