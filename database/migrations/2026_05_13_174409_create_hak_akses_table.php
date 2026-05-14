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
        Schema::create('hak_akses', function (Blueprint $table) {
            $table->id();
            $table->string('id_role_staf');
            $table->foreignId('modul_id')->constrained('modul')->onDelete('cascade');
            $table->boolean('bisa_buka')->default(false);
            $table->boolean('bisa_tambah')->default(false);
            $table->boolean('bisa_ubah')->default(false);
            $table->boolean('bisa_hapus')->default(false);
            $table->foreign('id_role_staf')->references('id_role_staf')->on('role_staf')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hak_akses');
    }
};
