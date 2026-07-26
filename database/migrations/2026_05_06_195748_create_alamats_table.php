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
        Schema::create('alamat', function (Blueprint $table) {
            $table->string('id_alamat')->primary();
            $table->string('id_customer');
            $table->string('label')->nullable();

            $table->string('nama_penerima');
            $table->string('no_hp');

            $table->unsignedInteger('provinsi_id')->nullable();
            $table->string('provinsi');
            $table->unsignedInteger('kota_id')->nullable();
            $table->string('kota');
            $table->unsignedInteger('kecamatan_id')->nullable();
            $table->string('kecamatan');

            $table->string('kode_pos');

            $table->text('alamat_lengkap');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreign('id_customer')->references('id_customer')->on('customer')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat');
    }
};
