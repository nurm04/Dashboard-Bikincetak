<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alamat', function (Blueprint $table) {

            $table->string('label')->nullable()->after('id_customer');

            $table->string('nama_penerima')->after('label');
            $table->string('no_hp')->after('nama_penerima');

            $table->string('provinsi')->after('no_hp');
            $table->string('kota')->after('provinsi');
            $table->string('kecamatan')->after('kota');
            $table->string('kelurahan')->nullable()->after('kecamatan');

            $table->string('kode_pos')->after('kelurahan');

            $table->renameColumn('alamat', 'alamat_lengkap');

            $table->decimal('latitude', 10, 7)
                ->nullable()
                ->after('alamat_lengkap');

            $table->decimal('longitude', 10, 7)
                ->nullable()
                ->after('latitude');

            $table->boolean('is_default')
                ->default(false)
                ->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('alamat', function (Blueprint $table) {

            $table->dropColumn([
                'label',
                'nama_penerima',
                'no_hp',
                'provinsi',
                'kota',
                'kecamatan',
                'kelurahan',
                'kode_pos',
                'latitude',
                'longitude',
                'is_default',
            ]);

            $table->renameColumn('alamat_lengkap', 'alamat');
        });
    }
};
