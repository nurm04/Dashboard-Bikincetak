<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pesanan_log', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesan');
            $table->string('id_staf', 50)->nullable();
            $table->string('aksi');
            $table->text('keterangan')->nullable();

            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();

            $table->timestamps();

            $table->foreign('id_pesan')->references('id_pesan')->on('pesan')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pesanan_log');
    }
};
