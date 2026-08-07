<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pesanan_item_produksi', function (Blueprint $table) {
            $table->string('id_staf_pelaksana', 50)->nullable()->after('id_vendor');
        });
    }

    public function down()
    {
        Schema::table('pesanan_item_produksi', function (Blueprint $table) {
            $table->dropColumn('id_staf_pelaksana');
        });
    }
};
