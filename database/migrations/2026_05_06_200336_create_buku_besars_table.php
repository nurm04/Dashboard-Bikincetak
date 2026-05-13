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
        Schema::create('buku_besar', function (Blueprint $table) {
            $table->string('id_buku_besar')->primary();
            $table->dateTime('tanggal_transaksi');
            $table->enum('tipe_referensi', ['pesanan', 'pembelian']);
            $table->string('id_referensi');
            $table->string('id_akun');
            $table->float('debit')->default(0);
            $table->float('kredit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_besar');
    }
};
