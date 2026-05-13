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
        Schema::create('staf', function (Blueprint $table) {
            $table->string('id_staf');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('no_hp');
            $table->string('id_role_staf');
            $table->foreign('id_role_staf')->references('id_role_staf')->on('role_staf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staf');
    }
};
