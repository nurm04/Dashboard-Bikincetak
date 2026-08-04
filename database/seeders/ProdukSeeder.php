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
            ['id_kategori' => 'KAT-000', 'nama_kategori' => 'Jasa', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-001', 'nama_kategori' => 'Print Digital', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-002', 'nama_kategori' => 'Cetak Sticker', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-003', 'nama_kategori' => 'Media Promosi', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-004', 'nama_kategori' => 'Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-005', 'nama_kategori' => 'Office Stationery', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-006', 'nama_kategori' => 'Cetak Buku', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-007', 'nama_kategori' => 'Cetak Kemasan', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-008', 'nama_kategori' => 'Merchandise', 'created_at' => $now, 'updated_at' => $now],
            ['id_kategori' => 'KAT-009', 'nama_kategori' => 'Kaos Sablon', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('varian')->insert([
            ['id_varian' => 'VAR-001', 'nama_varian' => 'Bahan', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-002', 'nama_varian' => 'Ukuran', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-003', 'nama_varian' => 'Bahan Sticker', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-004', 'nama_varian' => 'Ukuran Sticker', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-005', 'nama_varian' => 'Bahan Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-006', 'nama_varian' => 'Ukuran Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-007', 'nama_varian' => 'Ukuran Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-008', 'nama_varian' => 'Jumlah Lembar Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-010', 'nama_varian' => 'Bahan Stempel', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-012', 'nama_varian' => 'Bahan Paper Bag', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-013', 'nama_varian' => 'Bahan Tote Bag', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-015', 'nama_varian' => 'Bahan Gantungan Kunci', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-016', 'nama_varian' => 'Ukuran Gantungan Kunci', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-017', 'nama_varian' => 'Ukuran Pin Peniti', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-018', 'nama_varian' => 'Bahan Mug', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-019', 'nama_varian' => 'Bahan Kipas Promosi', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-020', 'nama_varian' => 'Ukuran Jam Dinding', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-021', 'nama_varian' => 'Bahan Puzzle', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-023', 'nama_varian' => 'Ukuran Mini Stand', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-024', 'nama_varian' => 'Ukuran Amplop', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-025', 'nama_varian' => 'Produk Lanyard', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-026', 'nama_varian' => 'Bahan Kop Surat', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-028', 'nama_varian' => 'Pulpen Ballpoint', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-029', 'nama_varian' => 'Bahan Nama Dada', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-030', 'nama_varian' => 'Tipe Flashdisk', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-031', 'nama_varian' => 'Ukuran Flashdisk', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-032', 'nama_varian' => 'Bahan Mouse Pad', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-033', 'nama_varian' => 'Ukuran Tiket Voucher', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-034', 'nama_varian' => 'Ukuran Kemasan Nasi', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-035', 'nama_varian' => 'Warna Baju', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-036', 'nama_varian' => 'Ukuran Baju', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-037', 'nama_varian' => 'Jenis Jersey', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-038', 'nama_varian' => 'Tipe Lengan', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-039', 'nama_varian' => 'Jasa Desain', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-040', 'nama_varian' => 'Ukuran Backdrop Portable Backwall', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-042', 'nama_varian' => 'Wobler', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-043', 'nama_varian' => 'Pop Up Table', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-044', 'nama_varian' => 'Event Desk Meja Promosi', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-045', 'nama_varian' => 'Ukuran Human Standee', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-047', 'nama_varian' => 'Jenis Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_varian' => 'VAR-048', 'nama_varian' => 'Bahan Bendera Umbul Umbul', 'created_at' => $now, 'updated_at' => $now],

            ['id_varian' => 'VAR-000', 'nama_varian' => 'Bahan Customer/BikinCetak', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('pilihan_varian')->insert([
            ['id_pilihan' => 'VAR-001-001', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Albatros', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-002', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Poster Albatros', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-003', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'HVS Hitam Putih', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-004', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'HVS Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-005', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'HVS 80 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-006', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'HVS 100 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-007', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Carton 210 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-008', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Carton 260 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-009', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Carton 310 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-010', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Paper 120 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-011', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Paper 150 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-012', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Photopaper 210 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-013', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Photopaper 230 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-014', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Poster Photopaper', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-015', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Kalkir', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-016', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Linen', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-017', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Jasmine', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-018', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Concorde', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-019', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Blueswhite', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-020', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Linen Laser', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-021', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Inkjet 100 Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-022', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'BC Tik', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-023', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Linen', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-024', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Art Paper 260 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-025', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Poster Art Paper 260 Gsm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-001-026', 'id_varian' => 'VAR-001', 'nama_pilihan' => 'Poster Art Paper 150 Gsm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-002-001', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A0', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-002', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A1', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-003', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A2', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-004', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A3', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-005', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A4', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-006', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A5', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-007', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'A6', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-002-008', 'id_varian' => 'VAR-002', 'nama_pilihan' => 'Sepertiga A4', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-003-001', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Chromo', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-002', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Chromo Bontak', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-003', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Vinyl', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-004', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Vinyl Putih', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-005', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Vinyl Silver', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-006', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Vinyl Transparan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-007', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'HVS', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-008', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'HVS Bontak', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-003-009', 'id_varian' => 'VAR-003', 'nama_pilihan' => 'Hologram', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-004-001', 'id_varian' => 'VAR-004', 'nama_pilihan' => '32 x 48 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-002', 'id_varian' => 'VAR-004', 'nama_pilihan' => '15 x 20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-003', 'id_varian' => 'VAR-004', 'nama_pilihan' => '10 x 20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-004', 'id_varian' => 'VAR-004', 'nama_pilihan' => '5 x 20 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-005', 'id_varian' => 'VAR-004', 'nama_pilihan' => '5 x 15 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-006', 'id_varian' => 'VAR-004', 'nama_pilihan' => '5 x 10 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-004-007', 'id_varian' => 'VAR-004', 'nama_pilihan' => '5 x 5 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-005-001', 'id_varian' => 'VAR-005', 'nama_pilihan' => 'Albatros', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-002', 'id_varian' => 'VAR-005', 'nama_pilihan' => 'Flexi China 280', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-003', 'id_varian' => 'VAR-005', 'nama_pilihan' => 'Flexi Jerman 510', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-004', 'id_varian' => 'VAR-005', 'nama_pilihan' => 'Flexi Korea 440', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-005', 'id_varian' => 'VAR-005', 'nama_pilihan' => 'Luster', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-005-006', 'id_varian' => 'VAR-005', 'nama_pilihan' => 'Photopaper', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-006-001', 'id_varian' => 'VAR-006', 'nama_pilihan' => '60 x 160 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-002', 'id_varian' => 'VAR-006', 'nama_pilihan' => '80 x 180 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-003', 'id_varian' => 'VAR-006', 'nama_pilihan' => '85 x 200 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-004', 'id_varian' => 'VAR-006', 'nama_pilihan' => '60 x 120 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-005', 'id_varian' => 'VAR-006', 'nama_pilihan' => 'A1', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-006', 'id_varian' => 'VAR-006', 'nama_pilihan' => 'A2', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-006-007', 'id_varian' => 'VAR-006', 'nama_pilihan' => 'A3', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-007-001', 'id_varian' => 'VAR-007', 'nama_pilihan' => '31x48 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-007-002', 'id_varian' => 'VAR-007', 'nama_pilihan' => '38x53 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-007-003', 'id_varian' => 'VAR-007', 'nama_pilihan' => '46x64 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-008-001', 'id_varian' => 'VAR-008', 'nama_pilihan' => '8 Lembar', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-008-002', 'id_varian' => 'VAR-008', 'nama_pilihan' => '13 Lembar', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-010-001', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Lingkaran Diameter 28 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-002', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Lingkaran Diameter 35 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-003', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Lingkaran Diameter 45 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-004', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Oval Diameter 45 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-005', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Oval Diameter 51 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-006', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Persegi 27x55 mm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-010-007', 'id_varian' => 'VAR-010', 'nama_pilihan' => 'Persegi 32x55 mm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-012-001', 'id_varian' => 'VAR-012', 'nama_pilihan' => 'Paper Bag', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-013-001', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Blacu', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-013-002', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Sublim', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-013-003', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Spunbond', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-013-004', 'id_varian' => 'VAR-013', 'nama_pilihan' => 'Kanvas', 'created_at' => $now, 'updated_at' => $now],

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

            ['id_pilihan' => 'VAR-023-001', 'id_varian' => 'VAR-023', 'nama_pilihan' => '50 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-023-002', 'id_varian' => 'VAR-023', 'nama_pilihan' => '75 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-023-003', 'id_varian' => 'VAR-023', 'nama_pilihan' => '100 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-024-001', 'id_varian' => 'VAR-024', 'nama_pilihan' => '11x23 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-025-001', 'id_varian' => 'VAR-025', 'nama_pilihan' => 'Stopper Tali <Leg></Leg>anyard', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-025-002', 'id_varian' => 'VAR-025', 'nama_pilihan' => 'Tali Lanyard ID Card', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-025-003', 'id_varian' => 'VAR-025', 'nama_pilihan' => 'Tali Yoyo ID Card', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-026-001', 'id_varian' => 'VAR-026', 'nama_pilihan' => 'HVS 80 Gsm Inkjet', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-026-002', 'id_varian' => 'VAR-026', 'nama_pilihan' => 'HVS 80 Gsm Laser', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-028-001', 'id_varian' => 'VAR-028', 'nama_pilihan' => 'Bolpoin', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-028-002', 'id_varian' => 'VAR-028', 'nama_pilihan' => 'Bolpoin UV Custom - 1 Sisi', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-029-001', 'id_varian' => 'VAR-029', 'nama_pilihan' => 'Cemiti Gravoply', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-030-001', 'id_varian' => 'VAR-030', 'nama_pilihan' => 'Flashdisk Kartu', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-030-002', 'id_varian' => 'VAR-030', 'nama_pilihan' => 'Flashdisk Promosi', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-031-001', 'id_varian' => 'VAR-031', 'nama_pilihan' => '4Gb', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-031-002', 'id_varian' => 'VAR-031', 'nama_pilihan' => '8Gb', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-031-003', 'id_varian' => 'VAR-031', 'nama_pilihan' => '16Gb', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-033-001', 'id_varian' => 'VAR-033', 'nama_pilihan' => '8x6 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-033-002', 'id_varian' => 'VAR-033', 'nama_pilihan' => '15x6 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-034-001', 'id_varian' => 'VAR-034', 'nama_pilihan' => '30x46 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-034-002', 'id_varian' => 'VAR-034', 'nama_pilihan' => '36x50 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-034-003', 'id_varian' => 'VAR-034', 'nama_pilihan' => '40x60 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-034-004', 'id_varian' => 'VAR-034', 'nama_pilihan' => '50x73 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-034-005', 'id_varian' => 'VAR-034', 'nama_pilihan' => '73x100 cm', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-035-001', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Hitam DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-002', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Putih DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-003', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Abu Grey DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-004', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Merah DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-005', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Pink DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-006', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Hijau Army DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-007', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Navy DTF', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-035-008', 'id_varian' => 'VAR-035', 'nama_pilihan' => 'Biru Muda DTF', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-036-001', 'id_varian' => 'VAR-036', 'nama_pilihan' => 'Up Size 2xl', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-036-002', 'id_varian' => 'VAR-036', 'nama_pilihan' => 'Up Size 3xl', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-036-003', 'id_varian' => 'VAR-036', 'nama_pilihan' => 'Up Size 4xl', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-036-004', 'id_varian' => 'VAR-036', 'nama_pilihan' => 'Up Size 5xl', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-037-001', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Bola', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-002', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Basket', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-003', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Voli', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-004', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Badminton', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-005', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Gamers', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-006', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Lari', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-007', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Sepeda', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-008', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Motocross', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-037-009', 'id_varian' => 'VAR-037', 'nama_pilihan' => 'Jersey Mancing', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-038-001', 'id_varian' => 'VAR-038', 'nama_pilihan' => 'Lengan Pendek', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-038-002', 'id_varian' => 'VAR-038', 'nama_pilihan' => 'Lengan Panjang', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-039-001', 'id_varian' => 'VAR-039', 'nama_pilihan' => 'Brosur', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-039-002', 'id_varian' => 'VAR-039', 'nama_pilihan' => 'Kalender', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-040-001', 'id_varian' => 'VAR-040', 'nama_pilihan' => '3x3 m', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-040-002', 'id_varian' => 'VAR-040', 'nama_pilihan' => '3x4 m', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-042-001', 'id_varian' => 'VAR-042', 'nama_pilihan' => 'Wobler', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-043-001', 'id_varian' => 'VAR-043', 'nama_pilihan' => 'Pop Up Table', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-044-001', 'id_varian' => 'VAR-044', 'nama_pilihan' => 'Meja Promosi', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-045-001', 'id_varian' => 'VAR-045', 'nama_pilihan' => '60x160 cm', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-045-002', 'id_varian' => 'VAR-045', 'nama_pilihan' => 'A1', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-045-003', 'id_varian' => 'VAR-045', 'nama_pilihan' => 'A2', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-045-004', 'id_varian' => 'VAR-045', 'nama_pilihan' => 'A3', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-045-005', 'id_varian' => 'VAR-045', 'nama_pilihan' => 'Custom', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-047-001', 'id_varian' => 'VAR-047', 'nama_pilihan' => 'Impraboard / Foamboard', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-047-002', 'id_varian' => 'VAR-047', 'nama_pilihan' => 'Tripod', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-048-001', 'id_varian' => 'VAR-048', 'nama_pilihan' => 'Kain Satin', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-048-002', 'id_varian' => 'VAR-048', 'nama_pilihan' => 'Kain TC', 'created_at' => $now, 'updated_at' => $now],

            ['id_pilihan' => 'VAR-000-002', 'id_varian' => 'VAR-000', 'nama_pilihan' => 'Bahan dari Pelanggan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan' => 'VAR-000-001', 'id_varian' => 'VAR-000', 'nama_pilihan' => 'Bahan dari Bikincetak', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('produk')->insert([
            ['id_produk' => 'PRD-0001', 'id_kategori' => 'KAT-000', 'nama_produk' => 'Custom', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-0002', 'id_kategori' => 'KAT-000', 'nama_produk' => 'Jasa Desain', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-1001', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A0', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1002', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A1', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1003', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A2', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1004', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A3', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1005', 'id_kategori' => 'KAT-001', 'nama_produk' => 'Print A4', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-2001', 'id_kategori' => 'KAT-002', 'nama_produk' => 'Stiker Label Kemasan', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2002', 'id_kategori' => 'KAT-002', 'nama_produk' => 'Stiker A3+', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-3001', 'id_kategori' => 'KAT-003', 'nama_produk' => 'X & Y Banner', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3002', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Roll Banner', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3003', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Backdrop Backwall', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3004', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Tent Card Akrilik', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3005', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Wobler', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3006', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Pop Up Table', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3007', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Event Desk', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3008', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Human Standee', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3009', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Tripod Banner', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3010', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Brosur Flyer', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3011', 'id_kategori' => 'KAT-003', 'nama_produk' => 'Bendera Umbul-Umbul', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-4001', 'id_kategori' => 'KAT-004', 'nama_produk' => 'Kalender Meja', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-4002', 'id_kategori' => 'KAT-004', 'nama_produk' => 'Kalender Dinding', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-5001', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Kartu Nama', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5002', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Stempel', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5003', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Amplop', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5004', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Lanyard', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5005', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Kop Surat A4', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5006', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Map', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5007', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Pulpen', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5008', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Nama Dada', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5009', 'id_kategori' => 'KAT-005', 'nama_produk' => 'Falshdisk', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-6001', 'id_kategori' => 'KAT-006', 'nama_produk' => 'Nota NCR', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6002', 'id_kategori' => 'KAT-006', 'nama_produk' => 'Cetak Buku', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6003', 'id_kategori' => 'KAT-006', 'nama_produk' => 'Blocknote', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6004', 'id_kategori' => 'KAT-006', 'nama_produk' => 'Tiket Voucher', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-7001', 'id_kategori' => 'KAT-007', 'nama_produk' => 'Paper Bag', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-7002', 'id_kategori' => 'KAT-007', 'nama_produk' => 'Kotak Nasi', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

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
            ['id_produk' => 'PRD-8011', 'id_kategori' => 'KAT-008', 'nama_produk' => 'Tote Bag', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-9001', 'id_kategori' => 'KAT-009', 'nama_produk' => 'Kaos Sablon', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9002', 'id_kategori' => 'KAT-009', 'nama_produk' => 'Kaos Polo', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9003', 'id_kategori' => 'KAT-009', 'nama_produk' => 'Hoodie Sweater', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9004', 'id_kategori' => 'KAT-009', 'nama_produk' => 'Jersey', 'gambar' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);


        DB::table('produk_varian')->insert([
            ['id_produk' => 'PRD-0002', 'id_varian' => 'VAR-039', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-1001', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1002', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1003', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1004', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-1005', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-2001', 'id_varian' => 'VAR-003', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2001', 'id_varian' => 'VAR-004', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2002', 'id_varian' => 'VAR-003', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-2002', 'id_varian' => 'VAR-004', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-3001', 'id_varian' => 'VAR-005', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3001', 'id_varian' => 'VAR-006', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3002', 'id_varian' => 'VAR-005', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3002', 'id_varian' => 'VAR-006', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3003', 'id_varian' => 'VAR-040', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3004', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3005', 'id_varian' => 'VAR-042', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3006', 'id_varian' => 'VAR-043', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3007', 'id_varian' => 'VAR-044', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3008', 'id_varian' => 'VAR-045', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3009', 'id_varian' => 'VAR-006', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3009', 'id_varian' => 'VAR-047', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3010', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-3011', 'id_varian' => 'VAR-048', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-4001', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-4001', 'id_varian' => 'VAR-008', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-4002', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-4002', 'id_varian' => 'VAR-007', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-5001', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5002', 'id_varian' => 'VAR-010', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5003', 'id_varian' => 'VAR-024', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5004', 'id_varian' => 'VAR-025', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5005', 'id_varian' => 'VAR-026', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5006', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5007', 'id_varian' => 'VAR-028', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5008', 'id_varian' => 'VAR-029', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5009', 'id_varian' => 'VAR-030', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-5009', 'id_varian' => 'VAR-031', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-6001', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6002', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6002', 'id_varian' => 'VAR-001', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6003', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-6004', 'id_varian' => 'VAR-033', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-7001', 'id_varian' => 'VAR-012', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-7002', 'id_varian' => 'VAR-034', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-8001', 'id_varian' => 'VAR-015', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8001', 'id_varian' => 'VAR-016', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8002', 'id_varian' => 'VAR-017', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8003', 'id_varian' => 'VAR-018', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8004', 'id_varian' => 'VAR-019', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8005', 'id_varian' => 'VAR-020', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8006', 'id_varian' => 'VAR-000', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8007', 'id_varian' => 'VAR-000', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8008', 'id_varian' => 'VAR-021', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8008', 'id_varian' => 'VAR-002', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8009', 'id_varian' => 'VAR-023', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8010', 'id_varian' => 'VAR-000', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-8011', 'id_varian' => 'VAR-013', 'created_at' => $now, 'updated_at' => $now],

            ['id_produk' => 'PRD-9001', 'id_varian' => 'VAR-035', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9001', 'id_varian' => 'VAR-036', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9002', 'id_varian' => 'VAR-035', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9003', 'id_varian' => 'VAR-035', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9003', 'id_varian' => 'VAR-036', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9004', 'id_varian' => 'VAR-037', 'created_at' => $now, 'updated_at' => $now],
            ['id_produk' => 'PRD-9004', 'id_varian' => 'VAR-038', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
