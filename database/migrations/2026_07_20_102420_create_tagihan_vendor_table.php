<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_vendor', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tagihan', 50)->unique()->nullable();
            $table->string('id_vendor', 50);
            $table->decimal('total_tagihan', 15, 2);

            $table->string('nama_bank', 50)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('atas_nama', 100)->nullable();

            $table->enum('status', ['belum_dibayar', 'lunas'])->default('belum_dibayar');
            $table->string('bukti_bayar', 255)->nullable();
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamps();

            $table->foreign('id_vendor')->references('id_vendor')->on('vendor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_vendor');
    }
};
