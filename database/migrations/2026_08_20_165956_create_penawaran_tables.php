<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABEL PENAWARAN (Induk)
        Schema::create('penawaran', function (Blueprint $table) {
            $table->string('id_penawaran')->primary(); // Format: PP-202607-90
            $table->string('kode_penawaran');
            $table->string('id_customer');
            $table->string('id_alamat')->nullable(); // Bisa nullable kalau belum tau mau dikirim kemana

            $table->timestamp('tanggal_penawaran')->useCurrent();
            $table->date('berlaku_sampai')->nullable(); // Penawaran biasanya punya masa kedaluwarsa (misal 7 hari)

            // Status khusus penawaran
            $table->enum('status_penawaran', ['draft', 'terkirim', 'disetujui', 'ditolak', 'kedaluwarsa'])->default('draft');

            // Link ke tabel pesan (Jika penawaran ini di-ACC dan diubah jadi pesanan)
            $table->string('id_pesan_terkait')->nullable();

            // Estimasi Biaya (Sama seperti pesanan)
            $table->string('kode_voucher')->nullable();
            $table->float('diskon_voucher_nominal')->default(0);
            $table->string('ekspedisi_nama')->nullable(); // Contoh: "Belum Termasuk Biaya Kirim"
            $table->string('ekspedisi_layanan')->nullable();
            $table->integer('harga_ongkir')->default(0);

            // Syarat & Ketentuan dari gambar klien lu
            $table->text('catatan_penawaran')->nullable();

            $table->string('sumber_penawaran', 50)->default('pos_kasir');

            // Relasi
            $table->foreign('id_customer')->references('id_customer')->on('customer');
            // Jika id_pesan_terkait mau direlasikan:
            // $table->foreign('id_pesan_terkait')->references('id_pesan')->on('pesan');

            $table->timestamps();
        });

        // 2. TABEL PENAWARAN ITEM (Rincian Produk)
        Schema::create('penawaran_item', function (Blueprint $table) {
            $table->id();
            $table->string('id_penawaran');
            $table->string('id_sku');
            $table->string('nama_produk_snapshot');
            $table->integer('jumlah');

            $table->json('atribut_custom_snapshot')->nullable();
            $table->float('harga_dasar_awal_snapshot')->default(0);
            $table->float('total_diskon_snapshot')->default(0);
            $table->json('rincian_diskon_snapshot')->nullable();
            $table->float('harga_satuan_snapshot');
            $table->float('hpp_satuan_snapshot')->default(0); // Buat itung estimasi laba

            $table->string('estimasi_pengerjaan_snapshot')->nullable();
            $table->float('harga_pengerjaan_snapshot')->default(0);
            $table->float('total_berat_snapshot')->default(0);

            $table->text('file_desain')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('id_sku')->references('id_sku')->on('produk_sku');
            $table->foreign('id_penawaran')->references('id_penawaran')->on('penawaran')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. TABEL PENAWARAN ITEM FINISHING (Rincian Finishing)
        Schema::create('penawaran_item_finishing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penawaran_item')->constrained('penawaran_item')->onDelete('cascade');
            $table->string('id_sku_finishing');
            $table->string('nama_finishing_snapshot');
            $table->float('harga_finishing_snapshot');
            $table->float('hpp_finishing_snapshot')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penawaran_item_finishing');
        Schema::dropIfExists('penawaran_item');
        Schema::dropIfExists('penawaran');
    }
};
