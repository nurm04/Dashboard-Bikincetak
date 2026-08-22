<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom baru ke harga_bertingkat
        Schema::table('harga_bertingkat', function (Blueprint $table) {
            if (!Schema::hasColumn('harga_bertingkat', 'pengerjaan')) {
                $table->string('pengerjaan')->default('Reguler')->after('id_sku');
            }
        });

        /*
         * OPSIONAL TAPI DIREKOMENDASIKAN JIKA ADA DATA LAMA:
         * Pindahkan data lama dari harga_pengerjaan ke harga_bertingkat
         * Jika lu nggak butuh data lama (karena mau import CSV baru),
         * lu bisa komen blok script DB::table di bawah ini.
         */
        $oldPengerjaans = DB::table('harga_pengerjaan')->get();
        foreach ($oldPengerjaans as $p) {
            DB::table('harga_bertingkat')->insert([
                'id_sku' => $p->id_sku,
                'pengerjaan' => $p->pengerjaan,
                'min' => 1, // Anggap rentang minimumnya 1
                'max' => 0, // Tak terhingga
                'tipe' => $p->tipe,
                'nilai' => $p->nilai,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. DROP TABLE harga_pengerjaan LAMA JIKA SUDAH AMAN
        Schema::dropIfExists('harga_pengerjaan');
    }

    public function down(): void
    {
        // Untuk rollback (jika dibutuhkan)
        Schema::create('harga_pengerjaan', function (Blueprint $table) {
            $table->id();
            $table->string('id_sku');
            $table->string('pengerjaan');
            $table->enum('tipe', ['nominal', 'persen'])->default('nominal');
            $table->float('nilai');
            $table->foreign('id_sku')->references('id_sku')->on('produk_sku')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('harga_bertingkat', function (Blueprint $table) {
            if (Schema::hasColumn('harga_bertingkat', 'pengerjaan')) {
                $table->dropColumn('pengerjaan');
            }
        });
    }
};
