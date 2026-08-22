<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            // Referensi ke staf lu
            $table->string('id_staf');
            $table->foreign('id_staf')->references('id_staf')->on('staf')->onDelete('cascade');

            $table->date('tanggal'); // Menyimpan tanggal spesifik, misal: 2026-08-14

            // Absen Masuk
            $table->time('jam_masuk')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->decimal('lat_masuk', 10, 8)->nullable(); // Menyimpan presisi GPS
            $table->decimal('long_masuk', 11, 8)->nullable();

            // Absen Pulang
            $table->time('jam_keluar')->nullable();
            $table->string('foto_keluar')->nullable();
            $table->decimal('lat_keluar', 10, 8)->nullable();
            $table->decimal('long_keluar', 11, 8)->nullable();

            // Status (Sesuai jam kerja 07:00 - 17:00)
            $table->enum('status', ['Tepat Waktu', 'Terlambat', 'Alpha', 'Izin', 'Sakit'])->default('Alpha');
            $table->text('keterangan');

            $table->timestamps();

            // Kunci: 1 Staf cuma boleh punya 1 baris per tanggal
            $table->unique(['id_staf', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
