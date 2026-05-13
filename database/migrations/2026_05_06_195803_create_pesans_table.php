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
        Schema::create('pesan', function (Blueprint $table) {
            $table->string('id_pesan')->primary();
            $table->string('id_customer');
            $table->string('id_alamat');
            $table->timestamp('tanggal_pesan')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('tanggal_selesai');
            $table->enum('status_operasional', ['keranjang', 'menunggu_diproses', 'proses_pengerjaan', 'selesai']);
            $table->enum('status_pembayaran', ['belum_lunas', 'lunas']);
            $table->string('estimasi_pengerjaan');
            $table->foreign('id_customer')->references('id_customer')->on('customer')->onDelete('cascade');
            $table->foreign('id_alamat')->references('id_alamat')->on('alamat')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan');
    }
};
