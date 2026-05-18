<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('id_pembayaran')->unique();
            $table->string('id_pesan');
            $table->float('nominal_bayar');
            $table->string('metode_pembayaran');
            $table->enum('status_pembayaran', ['menunggu_pembayaran', 'berhasil', 'gagal', 'kadaluarsa'])->default('menunggu_pembayaran');
            // ============== FIELD PENTING UNTUK MIDTRANS & GATEWAY ==============
            $table->string('snap_token')->nullable(); // Token dari Midtrans untuk load halaman popup Snap Vue
            $table->string('reference_id')->nullable(); // ID Transaksi resmi dari pihak Midtrans/Bank luar (order_id / transaction_id)
            $table->string('payment_type_detail')->nullable(); // Detail otomatis dari gateway (Contoh: gopay, shopeepay, bca_va)
            // =====================================================================
            $table->string('id_staf')->nullable();
            $table->text('catatan')->nullable();
            $table->foreign('id_pesan')->references('id_pesan')->on('pesan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
