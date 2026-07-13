<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher', function (Blueprint $table) {
            $table->id('id_voucher');
            $table->string('kode_voucher')->unique();
            $table->string('nama_promo');

            $table->enum('tipe_target', ['semua_pesanan', 'produk_tertentu'])->default('semua_pesanan');

            $table->string('id_sku_target')->nullable();

            $table->decimal('persentase_diskon', 5, 2);

            $table->integer('maksimal_potongan_rupiah')->nullable();
            $table->integer('minimal_transaksi_rupiah')->default(0);

            $table->integer('kuota_penggunaan')->nullable();
            $table->dateTime('berlaku_dari');
            $table->dateTime('berlaku_sampai');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->foreign('id_sku_target')->references('id_sku')->on('produk_sku')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher');
    }
};
