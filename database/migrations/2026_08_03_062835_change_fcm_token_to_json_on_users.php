<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Kosongkan dulu token lama biar gak error parsing JSON
        DB::table('users')->update(['fcm_token' => null]);

        Schema::table('users', function (Blueprint $table) {
            // Ubah tipe kolom jadi JSON
            $table->json('fcm_token')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->change();
        });
    }
};
