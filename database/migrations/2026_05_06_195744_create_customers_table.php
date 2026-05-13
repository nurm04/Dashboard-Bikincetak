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
        Schema::create('customer', function (Blueprint $table) {
            $table->string('id_customer')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('no_hp');
            $table->string('id_role_customer');
            $table->foreign('id_role_customer')->references('id_role_customer')->on('role_customer')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
