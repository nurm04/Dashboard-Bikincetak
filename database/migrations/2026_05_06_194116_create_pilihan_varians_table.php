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
        Schema::create('pilihan_varian', function (Blueprint $table) {
            $table->string('id_pilihan')->primary();
            $table->string('id_varian');
            $table->string('nama_pilihan');
            $table->foreign('id_varian')->references('id_varian')->on('varian')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihan_varian');
    }
};
