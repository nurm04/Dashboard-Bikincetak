<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TAHAP 1: Buatkan index biasa agar Foreign Key punya "pegangan"
        Schema::table('absensi', function (Blueprint $table) {
            $table->index('id_staf');
        });

        // TAHAP 2: Sekarang kita bisa aman merombak tabel
        Schema::table('absensi', function (Blueprint $table) {
            // Hapus unique menggunakan nama persis dari pesan error Anda
            $table->dropUnique('absensi_id_staf_tanggal_unique');

            // Tambahkan tipe absen
            $table->enum('tipe_absen', ['Reguler', 'Lembur'])->default('Reguler')->after('tanggal');

            // Buat unique constraint yang baru
            $table->unique(['id_staf', 'tanggal', 'tipe_absen']);
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropUnique(['id_staf', 'tanggal', 'tipe_absen']);
            $table->dropColumn('tipe_absen');

            $table->unique(['id_staf', 'tanggal']);
        });

        Schema::table('absensi', function (Blueprint $table) {
            // Hapus index cadangan saat rollback
            $table->dropIndex(['id_staf']);
        });
    }
};
