<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('modul')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('modul')->insert([
            ['id' => 1, 'nama_modul' => 'Hak Akses', 'slug' => 'hak-akses', 'created_at' => '2026-05-16 03:42:00', 'updated_at' => '2026-05-16 03:42:00'],
            ['id' => 2, 'nama_modul' => 'Akun', 'slug' => 'akun', 'created_at' => '2026-05-16 03:42:46', 'updated_at' => '2026-05-16 03:42:46'],
            ['id' => 3, 'nama_modul' => 'Customer', 'slug' => 'customer', 'created_at' => '2026-05-16 03:43:10', 'updated_at' => '2026-05-16 03:43:10'],
            ['id' => 4, 'nama_modul' => 'Staf', 'slug' => 'staf', 'created_at' => '2026-05-16 03:43:27', 'updated_at' => '2026-05-16 03:43:27'],
            ['id' => 5, 'nama_modul' => 'Kategori', 'slug' => 'kategori', 'created_at' => '2026-05-16 03:43:57', 'updated_at' => '2026-05-16 03:43:57'],
            ['id' => 6, 'nama_modul' => 'Produk', 'slug' => 'produk', 'created_at' => '2026-05-16 03:44:22', 'updated_at' => '2026-05-16 03:44:22'],
            ['id' => 7, 'nama_modul' => 'Produk Sku', 'slug' => 'produk-sku', 'created_at' => '2026-05-16 03:44:41', 'updated_at' => '2026-05-16 03:44:41'],
            ['id' => 8, 'nama_modul' => 'Varian', 'slug' => 'varian', 'created_at' => '2026-05-16 03:44:59', 'updated_at' => '2026-05-16 03:44:59'],
            ['id' => 9, 'nama_modul' => 'Finishing', 'slug' => 'finishing', 'created_at' => '2026-05-16 03:45:15', 'updated_at' => '2026-05-16 03:45:15'],
            ['id' => 10, 'nama_modul' => 'Bahan Baku', 'slug' => 'bahan-baku', 'created_at' => '2026-05-16 03:47:12', 'updated_at' => '2026-05-16 03:47:12'],
            ['id' => 11, 'nama_modul' => 'Pembelian Bahan', 'slug' => 'pembelian-bahan', 'created_at' => '2026-05-16 03:47:39', 'updated_at' => '2026-05-16 03:47:39'],
            ['id' => 12, 'nama_modul' => 'Pesan', 'slug' => 'pesan', 'created_at' => '2026-05-16 06:06:54', 'updated_at' => '2026-05-16 06:06:54'],
            ['id' => 13, 'nama_modul' => 'Alamat', 'slug' => 'alamat', 'created_at' => '2026-05-16 06:07:59', 'updated_at' => '2026-05-16 06:07:59'],
            ['id' => 14, 'nama_modul' => 'Pembayaran', 'slug' => 'pembayaran', 'created_at' => '2026-05-16 06:06:59', 'updated_at' => '2026-05-16 06:06:59'],
            ['id' => 15, 'nama_modul' => 'Voucher', 'slug' => 'voucher', 'created_at' => '2026-05-16 06:07:59', 'updated_at' => '2026-05-16 06:07:59'],
            ['id' => 16, 'nama_modul' => 'Vendor', 'slug' => 'vendor', 'created_at' => '2026-05-16 06:07:59', 'updated_at' => '2026-05-16 06:07:59'],
            ['id' => 17, 'nama_modul' => 'produksi', 'slug' => 'produksi', 'created_at' => '2026-05-16 06:07:59', 'updated_at' => '2026-05-16 06:07:59'],
            ['id' => 18, 'nama_modul' => 'Tagihan Vendor', 'slug' => 'tagihan-vendor', 'created_at' => '2026-05-16 06:07:59', 'updated_at' => '2026-05-16 06:07:59'],
            ['id' => 19, 'nama_modul' => 'File Manage', 'slug' => 'file-manage', 'created_at' => '2026-05-16 06:07:59', 'updated_at' => '2026-05-16 06:07:59'],
        ]);
    }
}
