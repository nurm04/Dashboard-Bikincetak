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
        Schema::create('sku_finishing_harga_bertingkat', function (Blueprint $table) {
            $table->id();

            // Foreign key nembak ke id tabel sku_finishing
            $table->unsignedBigInteger('sku_finishing_id');

            // Rentang kuantitas grosir
            $table->integer('min');
            $table->integer('max')->default(0)->comment('Isi 0 untuk tak terhingga (Lebih dari)');

            // Tipe dan Nilai harga grosir
            $table->enum('tipe', ['nominal', 'persen'])->default('nominal');
            $table->float('nilai'); // Pakai float/decimal menyesuaikan tipe data di tabel lain

            $table->timestamps();

            // Relasi & Cascade Delete
            $table->foreign('sku_finishing_id')
                  ->references('id')
                  ->on('sku_finishing')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_finishing_harga_bertingkat');
    }
};
