<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_web', function (Blueprint $table) {
            $table->id();
            $table->string('grup')->default('general');
            $table->string('kunci')->unique();
            $table->longText('nilai')->nullable();
            $table->string('tipe_data')->default('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_web');
    }
};
