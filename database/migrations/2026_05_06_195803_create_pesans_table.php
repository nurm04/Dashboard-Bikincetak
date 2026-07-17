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
            $table->string('kode_transaksi');
            $table->string('id_customer');
            $table->string('id_alamat');
            $table->timestamp('tanggal_pesan')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->enum('status_operasional', ['keranjang', 'menunggu_diproses', 'proses_pengerjaan', 'proses_pengantaran', 'selesai', 'batal']);
            $table->enum('status_pembayaran', ['belum_lunas', 'dibayar_sebagian', 'lunas']);
            $table->dateTime('waktu_deadline')->nullable();
            $table->string('kode_voucher')->nullable();
            $table->float('diskon_voucher_nominal')->default(0);
            $table->string('ekspedisi_nama')->nullable();
            $table->string('ekspedisi_layanan')->nullable();
            $table->integer('harga_ongkir')->default(0);
            $table->string('ekspedisi_estimasi')->nullable();
            $table->string('nomor_resi')->nullable();
            $table->foreign('id_customer')->references('id_customer')->on('customer');
            $table->foreign('id_alamat')->references('id_alamat')->on('alamat');
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
