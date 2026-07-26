<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('kategori')->insert([
            ['id_kategori' => 'KAT-001', 'nama_kategori' => 'Print Digital', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-002', 'nama_kategori' => 'Cetak Sticker', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-003', 'nama_kategori' => 'Media Promosi', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-004', 'nama_kategori' => 'Souvenir & Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-005', 'nama_kategori' => 'Office Stationery', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-006', 'nama_kategori' => 'Buku & Form Bisnis', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-007', 'nama_kategori' => 'Kemasan & Apparel', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-008', 'nama_kategori' => 'Merchandise', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('varian')->insert([
            ['id_varian' => 'VAR-001', 'nama_varian' => 'Print A0-A4', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-002', 'nama_varian' => 'Bahan Sticker', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-003', 'nama_varian' => 'Ukuran Sticker', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-004', 'nama_varian' => 'Bahan Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-005', 'nama_varian' => 'Ukuran Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-006', 'nama_varian' => 'Bahan Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-007', 'nama_varian' => 'Jumlah Lembar Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-009', 'nama_varian' => 'Bahan Kartu Nama', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-010', 'nama_varian' => 'Bahan Stempel', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-011', 'nama_varian' => 'Bahan Nota NCR', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-012', 'nama_varian' => 'Bahan Paper Bag', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-013', 'nama_varian' => 'Bahan Tote Bag', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-014', 'nama_varian' => 'Ukuran Tote Bag', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-015', 'nama_varian' => 'Bahan Gantungan Kunci', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-016', 'nama_varian' => 'Ukuran Gantungan Kunci', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-017', 'nama_varian' => 'Ukuran Pin Peniti', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-018', 'nama_varian' => 'Bahan Mug', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-019', 'nama_varian' => 'Bahan Kipas Promosi', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-020', 'nama_varian' => 'Ukuran Jam Dinding', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-021', 'nama_varian' => 'Bahan Puzzle', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-022', 'nama_varian' => 'Ukuran Puzzle', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-023', 'nama_varian' => 'Ukuran Mini Stand', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-000', 'nama_varian' => 'Bahan Customer/BikinCetak', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('pilihan_varian')->insert([
            ['id_pilihan' => 'VAR-001-001', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Albatros', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-002', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Poster Albatros', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-003', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'HVS 80 Putih', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-004', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'HVS 80 Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-005', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Kalkir', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-006', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Carton 210gr', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-007', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Carton 260gr', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-008', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Carton 310gr', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-009', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Paper 120gr', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-010', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Paper 150gr', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-011', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Photopaper 210', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-012', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Photopaper 230', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-013', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Poster Photopaper', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-014', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Linen', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-015', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Jasmin', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-016', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Concorde', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-017', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Blueswhite', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-018', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'BC Tik', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-019', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Linen', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-020', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Linen Laser', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-021', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Inkjet 100 Warna', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-002-001', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'Chromo', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-002', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'Vinyl', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-003', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'Vinyl Putih', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-004', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'Vinyl Silver', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-005', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'Vinyl Transparan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-006', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'HVS', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-003-001', 'id_varian' => 'VAR-003', 'nama_pilihan' => '32 x 48 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-002', 'id_varian' => 'VAR-003', 'nama_pilihan' => '15 x 20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-003', 'id_varian' => 'VAR-003', 'nama_pilihan' => '10 x 20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-004', 'id_varian' => 'VAR-003', 'nama_pilihan' => '5 x 20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-005', 'id_varian' => 'VAR-003', 'nama_pilihan' => '5 x 15 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-006', 'id_varian' => 'VAR-003', 'nama_pilihan' => '5 x 10 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-007', 'id_varian' => 'VAR-003', 'nama_pilihan' => '5 x 5 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-004-001', 'id_varian' => 'VAR-004', 'nama_pilihan' => 'Albatros', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-002', 'id_varian' => 'VAR-004', 'nama_pilihan' => 'Flexi China 280', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-003', 'id_varian' => 'VAR-004', 'nama_pilihan' => 'Flexi Jerman 510', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-004', 'id_varian' => 'VAR-004', 'nama_pilihan' => 'Flexi Korea 440', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-005', 'id_varian' => 'VAR-004', 'nama_pilihan' => 'Luster', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-006', 'id_varian' => 'VAR-004', 'nama_pilihan' => 'Photopaper', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-005-001', 'id_varian' => 'VAR-005', 'nama_pilihan' => '60 x 160 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-002', 'id_varian' => 'VAR-005', 'nama_pilihan' => '80 x 180 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-003', 'id_varian' => 'VAR-005', 'nama_pilihan' => '85 x 200 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-006-001', 'id_varian' => 'VAR-006', 'nama_pilihan' => 'AC210', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-002', 'id_varian' => 'VAR-006', 'nama_pilihan' => 'AC260', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-007-001', 'id_varian' => 'VAR-007', 'nama_pilihan' => '8 Lembar', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-007-002', 'id_varian' => 'VAR-007', 'nama_pilihan' => '13 Lembar', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-009-001', 'id_varian' => 'VAR-009', 'nama_pilihan' => 'AC260', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-009-002', 'id_varian' => 'VAR-009', 'nama_pilihan' => 'Jasmin', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-010-001', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Lingkaran Diameter 28 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-002', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Lingkaran Diameter 35 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-003', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Lingkaran Diameter 45 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-004', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Oval Diameter 45 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-005', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Oval Diameter 51 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-006', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Persegi 27x55 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-007', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Persegi 32x55 mm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-011-001', 'id_varian' => 'VAR-011', 'nama_pilihan' => 'NCR A4', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-011-002', 'id_varian' => 'VAR-011', 'nama_pilihan' => 'NCR A5', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-011-003', 'id_varian' => 'VAR-011', 'nama_pilihan' => 'NCR A6', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-011-004', 'id_varian' => 'VAR-011', 'nama_pilihan' => 'NCR Sepertiga A4', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-012-001', 'id_varian' => 'VAR-012', 'nama_pilihan' => 'Paper Bag', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-013-001', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Blacu', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-013-002', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Sublim', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-013-003', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Spunbond', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-013-004', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Kanvas', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-014-001', 'id_varian' => 'VAR-014', 'nama_pilihan' => 'A4', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-014-002', 'id_varian' => 'VAR-014', 'nama_pilihan' => 'A5', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-014-003', 'id_varian' => 'VAR-014', 'nama_pilihan' => 'A6', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-015-001', 'id_varian' => 'VAR-015', 'nama_pilihan' => 'Akrilik', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-015-002', 'id_varian' => 'VAR-015', 'nama_pilihan' => 'Akrilik Grafir', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-015-003', 'id_varian' => 'VAR-015', 'nama_pilihan' => 'Pin', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-016-001', 'id_varian' => 'VAR-016', 'nama_pilihan' => '20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-016-002', 'id_varian' => 'VAR-016', 'nama_pilihan' => '30 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-016-003', 'id_varian' => 'VAR-016', 'nama_pilihan' => '40 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-016-004', 'id_varian' => 'VAR-016', 'nama_pilihan' => '50 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-016-005', 'id_varian' => 'VAR-016', 'nama_pilihan' => '58 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-016-006', 'id_varian' => 'VAR-016', 'nama_pilihan' => '70 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-017-001', 'id_varian' => 'VAR-017', 'nama_pilihan' => '44 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-017-002', 'id_varian' => 'VAR-017', 'nama_pilihan' => '58 mm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-018-001', 'id_varian' => 'VAR-018', 'nama_pilihan' => 'Mug Putih', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-019-001', 'id_varian' => 'VAR-019', 'nama_pilihan' => 'Kertas Custom', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-019-002', 'id_varian' => 'VAR-019', 'nama_pilihan' => 'Plastik Custom', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-020-001', 'id_varian' => 'VAR-020', 'nama_pilihan' => '28 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-020-002', 'id_varian' => 'VAR-020', 'nama_pilihan' => '32 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-021-001', 'id_varian' => 'VAR-021', 'nama_pilihan' => 'Akrilik', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-022-001', 'id_varian' => 'VAR-022', 'nama_pilihan' => 'A4', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-022-002', 'id_varian' => 'VAR-022', 'nama_pilihan' => 'A5', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-023-001', 'id_varian' => 'VAR-023', 'nama_pilihan' => '50 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-023-002', 'id_varian' => 'VAR-023', 'nama_pilihan' => '75 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-023-003', 'id_varian' => 'VAR-023', 'nama_pilihan' => '100 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-000-002', 'id_varian' => 'VAR-000', 'nama_pilihan' => 'Bahan Pelanggan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-000-001', 'id_varian' => 'VAR-000', 'nama_pilihan' => 'Bahan dari Bikincetak', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('produk')->insert([
            ['id_produk' => 'PRD-1001', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A0', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1002', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A1', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1003', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A2', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1004', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A3', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1005', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A4', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-2001', 'id_kategori' => 'KAT-002', 'nama_produk' => 'Stiker Label Kemasan', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2002', 'id_kategori' => 'KAT-002', 'nama_produk' => 'Stiker A3+', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-3001', 'id_kategori' => 'KAT-003', 'nama_produk' => 'X & Y Banner', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3002', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Roll Banner', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-4001', 'id_kategori' => 'KAT-004', 'nama_produk' => 'Kalender Meja', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-5001', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Kartu nama', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5002', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Stempel', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-6001', 'id_kategori' => 'KAT-006', 'nama_produk' => 'Buku Nota NCR', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-7001', 'id_kategori' => 'KAT-007', 'nama_produk' => 'Paper Bag', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-7002', 'id_kategori' => 'KAT-007', 'nama_produk' => 'Tote Bag', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-8001', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Gantungan Kunci', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8002', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Pin Peniti', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8003', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Mug', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8004', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Kipas Promosi', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8005', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Jam Dinding', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8006', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Print EToll Custom', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8007', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Korek Api Custom', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8008', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Puzzle Custom', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8009', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Mini Stand Akrilik', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8010', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Casing Handphone Custom', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);


        DB::table('produk_varian')->insert([
            ['id_produk' => 'PRD-1001', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1002', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1003', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1004', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1005', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-2001', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2001', 'id_varian' => 'VAR-003', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2002', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2002', 'id_varian' => 'VAR-003', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-3001', 'id_varian' => 'VAR-004', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3001', 'id_varian' => 'VAR-005', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3002', 'id_varian' => 'VAR-004', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3002', 'id_varian' => 'VAR-005', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-4001', 'id_varian' => 'VAR-006', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-4001', 'id_varian' => 'VAR-007', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-5001', 'id_varian' => 'VAR-009', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5002', 'id_varian' => 'VAR-010', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-6001', 'id_varian' => 'VAR-011', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-7001', 'id_varian' => 'VAR-012', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-7002', 'id_varian' => 'VAR-013', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-7002', 'id_varian' => 'VAR-014', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-8001', 'id_varian' => 'VAR-015', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8001', 'id_varian' => 'VAR-016', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8002', 'id_varian' => 'VAR-017', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8003', 'id_varian' => 'VAR-018', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8004', 'id_varian' => 'VAR-019', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8005', 'id_varian' => 'VAR-020', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8006', 'id_varian' => 'VAR-000', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8007', 'id_varian' => 'VAR-000', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8008', 'id_varian' => 'VAR-021', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8008', 'id_varian' => 'VAR-022', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8009', 'id_varian' => 'VAR-023', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8010', 'id_varian' => 'VAR-000', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
