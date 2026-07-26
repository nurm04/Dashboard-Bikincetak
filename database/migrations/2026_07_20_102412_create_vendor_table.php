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
        Schema::create('vendor', function (Blueprint $table) {
            $table->string('id_vendor', 50)->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_vendor', 150);
            $table->string('nama_pic', 100)->nullable();
            $table->string('no_hp', 20);
            $table->text('alamat_lengkap')->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('atas_nama', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor');
    }
};
