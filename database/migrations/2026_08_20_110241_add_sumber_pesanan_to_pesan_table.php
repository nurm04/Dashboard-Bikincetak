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
        Schema::table('pesan', function (Blueprint $table) {
            // Tambahkan field sumber_pesanan setelah kolom tanggal_selesai (atau sesuaikan)
            // Kasih default 'pos_kasir' untuk data lama yang udah ada di database lu.
            $table->string('sumber_pesanan', 50)->default('pos_kasir')->after('tanggal_selesai')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesan', function (Blueprint $table) {
            // Hapus field saat rollback
            $table->dropColumn('sumber_pesanan');
        });
    }
};
