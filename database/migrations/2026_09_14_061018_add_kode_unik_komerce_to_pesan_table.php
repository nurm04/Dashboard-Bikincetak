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
        Schema::table('pesan', function (Blueprint $table) {
            // Nambahin kolom kode_unik_komerce dengan nilai default 0
            $table->integer('kode_unik_komerce')->default(0)->after('status_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesan', function (Blueprint $table) {
            // Rollback kalau terjadi apa-apa
            $table->dropColumn('kode_unik_komerce');
        });
    }
};
