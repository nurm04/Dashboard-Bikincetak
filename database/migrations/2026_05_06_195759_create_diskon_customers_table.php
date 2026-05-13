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
        Schema::create('diskon_customer', function (Blueprint $table) {
            $table->id();
            $table->string('id_sku');
            $table->string('id_role_customer');
            $table->enum('tipe', ['nominal', 'persen'])->default('persen');
            $table->float('nilai');
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
            $table->foreign('id_role_customer')->references('id_role_customer')->on('role_customer')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskon_customer');
    }
};
