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
        Schema::create('pesanan_item_produksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pesanan_item')->constrained('pesanan_item')->cascadeOnDelete();
            $table->enum('tipe_pengerjaan', ['sendiri', 'vendor']);
            $table->string('id_vendor', 50)->nullable();
            $table->integer('qty_dikerjakan');
            $table->enum('status_pengerjaan', ['menunggu', 'sedang_diproses', 'selesai'])->default('menunggu');
            $table->text('deskripsi_pengerjaan')->nullable();
            $table->decimal('total_tagihan_vendor', 15, 2)->nullable();
            $table->string('file_nota', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_vendor')->references('id_vendor')->on('vendor')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_item_produksi');
    }
};
