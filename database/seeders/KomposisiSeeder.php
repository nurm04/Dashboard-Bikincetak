<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KomposisiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('komposisi')->insert([
            // // ==========================================
            // // KOMPOSISI PRINT A0 (Luas 1 m2 -> Pemakaian = 1)
            // // ==========================================
            // [
            //     'id_sku' => 'PRD-1001-SKU-001', 'id_bahan_baku' => 'BAHAN-0026', // A0 Albatros
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 22000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1001-SKU-002', 'id_bahan_baku' => 'BAHAN-0026', // A0 Poster Albatros
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 22000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1001-SKU-003', 'id_bahan_baku' => 'BAHAN-0028', // A0 HVS B&W
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 4500, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1001-SKU-004', 'id_bahan_baku' => 'BAHAN-0028', // A0 HVS Warna
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 4500, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1001-SKU-005', 'id_bahan_baku' => 'BAHAN-0029', // A0 Kalkir
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 12000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1001-SKU-006', 'id_bahan_baku' => 'BAHAN-0030', // A0 Poster Photopaper
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 18000, 'created_at' => $now, 'updated_at' => $now
            // ],

            // // ==========================================
            // // KOMPOSISI PRINT A1 (Luas 0.5 m2 -> Pemakaian = 0.5)
            // // ==========================================
            // [
            //     'id_sku' => 'PRD-1002-SKU-001', 'id_bahan_baku' => 'BAHAN-0026', // A1 Poster Albatros
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 11000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1002-SKU-002', 'id_bahan_baku' => 'BAHAN-0028', // A1 HVS B&W
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 2250, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1002-SKU-003', 'id_bahan_baku' => 'BAHAN-0028', // A1 HVS Warna
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 2250, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1002-SKU-004', 'id_bahan_baku' => 'BAHAN-0029', // A1 Kalkir
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 6000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1002-SKU-005', 'id_bahan_baku' => 'BAHAN-0031', // A1 Art Paper 150
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 4000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1002-SKU-006', 'id_bahan_baku' => 'BAHAN-0032', // A1 Art Paper 260
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 5500, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1002-SKU-007', 'id_bahan_baku' => 'BAHAN-0030', // A1 Poster Photopaper
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 9000, 'created_at' => $now, 'updated_at' => $now
            // ],

            // // ==========================================
            // // KOMPOSISI PRINT A2 (Luas 0.25 m2 -> Pemakaian = 0.25)
            // // ==========================================
            // [
            //     'id_sku' => 'PRD-1003-SKU-001', 'id_bahan_baku' => 'BAHAN-0028', // A2 HVS B&W
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 1125, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1003-SKU-002', 'id_bahan_baku' => 'BAHAN-0028', // A2 HVS Warna
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 1125, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1003-SKU-003', 'id_bahan_baku' => 'BAHAN-0026', // A2 Poster Albatros
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 5500, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1003-SKU-004', 'id_bahan_baku' => 'BAHAN-0031', // A2 Poster Art Paper 150
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 2000, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1003-SKU-005', 'id_bahan_baku' => 'BAHAN-0032', // A2 Poster Art Paper 260
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 2750, 'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_sku' => 'PRD-1003-SKU-006', 'id_bahan_baku' => 'BAHAN-0030', // A2 Poster Photopaper
            //     'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 4500, 'created_at' => $now, 'updated_at' => $now
            // ],
            // // ==============================================================================
            // // KOMPOSISI BAHAN BAKU: PRD-2001 (Stiker Label Kemasan - Jual Per Lembar A3+)
            // // ==============================================================================

            // // --- Kategori: Chromo Bontak (Menggunakan BAHAN-0018 | HPP: 850 x 1 = 850) ---
            // ['id_sku' => 'PRD-2001-SKU-001', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now], // 5 x 5 cm
            // ['id_sku' => 'PRD-2001-SKU-002', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now], // 5 x 10 cm
            // ['id_sku' => 'PRD-2001-SKU-003', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now], // 5 x 15 cm
            // ['id_sku' => 'PRD-2001-SKU-004', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now], // 5 x 20 cm
            // ['id_sku' => 'PRD-2001-SKU-005', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now], // 10 x 20 cm
            // ['id_sku' => 'PRD-2001-SKU-006', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now], // 15 x 20 cm

            // // --- Kategori: Vinyl Putih (Menggunakan BAHAN-0019 | HPP: 2500 x 1 = 2500) ---
            // // Note: Asumsi Label Vinyl standar menggunakan Vinyl Putih.
            // ['id_sku' => 'PRD-2001-SKU-007', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // 5 x 5 cm
            // ['id_sku' => 'PRD-2001-SKU-008', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // 5 x 10 cm
            // ['id_sku' => 'PRD-2001-SKU-009', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // 5 x 15 cm
            // ['id_sku' => 'PRD-2001-SKU-010', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // 5 x 20 cm
            // ['id_sku' => 'PRD-2001-SKU-011', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // 10 x 20 cm
            // ['id_sku' => 'PRD-2001-SKU-012', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // 15 x 20 cm


            // // ==============================================================================
            // // KOMPOSISI BAHAN BAKU: PRD-2002 (Stiker A3+ / Lembaran Tanpa Cutting Spesifik)
            // // ==============================================================================

            // // Note: Bahan Stiker Hologram belum ada di daftar Master Bahan Baku.
            // // Saya beri ID dummy 'BAHAN-XXXX', silakan sesuaikan dengan ID asli + HPP-nya nanti.
            // ['id_sku' => 'PRD-2002-SKU-001', 'id_bahan_baku' => 'BAHAN-0033', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 0, 'created_at' => $now, 'updated_at' => $now], // Hologram

            // // Cromo Bontak -> BAHAN-0018 (Harga Beli: 850)
            // ['id_sku' => 'PRD-2002-SKU-002', 'id_bahan_baku' => 'BAHAN-0018', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now],

            // // HVS Bontak -> BAHAN-0022 (Harga Beli: 900)
            // ['id_sku' => 'PRD-2002-SKU-003', 'id_bahan_baku' => 'BAHAN-0022', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 900, 'created_at' => $now, 'updated_at' => $now],

            // // Vinyl Putih -> BAHAN-0019 (Harga Beli: 2500)
            // ['id_sku' => 'PRD-2002-SKU-004', 'id_bahan_baku' => 'BAHAN-0019', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now],

            // // Vinyl Silver -> BAHAN-0021 (Harga Beli: 3000)
            // ['id_sku' => 'PRD-2002-SKU-005', 'id_bahan_baku' => 'BAHAN-0021', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 3000, 'created_at' => $now, 'updated_at' => $now],

            // // Vinyl Transparan -> BAHAN-0020 (Harga Beli: 2500)
            // ['id_sku' => 'PRD-2002-SKU-006', 'id_bahan_baku' => 'BAHAN-0020', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now],

            // // --- Laminasi Doff (FIN-001-001) ---
            // ['id_sku' => 'PRD-1001-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 9500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1001-SKU-002', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 9500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1001-SKU-006', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 9500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1002-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.5, 'hpp' => 4750, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1002-SKU-007', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.5, 'hpp' => 4750, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1003-SKU-003', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.25, 'hpp' => 2375, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1003-SKU-006', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.25, 'hpp' => 2375, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-001', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-002', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-003', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-005', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-014', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-002', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-003', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-004', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-006', 'id_bahan_baku' => 'BAHAN-0035', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],

            // // --- Laminasi Glossy (FIN-001-002) ---
            // ['id_sku' => 'PRD-1001-SKU-001', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 8000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1001-SKU-002', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 8000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1001-SKU-006', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 8000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1002-SKU-001', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.5, 'hpp' => 4000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1002-SKU-007', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.5, 'hpp' => 4000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1003-SKU-003', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.25, 'hpp' => 2000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1003-SKU-006', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.25, 'hpp' => 2000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-001', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-002', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-003', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-005', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-1004-SKU-014', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-002', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-003', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-004', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-2002-SKU-006', 'id_bahan_baku' => 'BAHAN-0034', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1, 'hpp' => 400, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-3001 (X & Y BANNER)
            // // ==============================================================================

            // // 1. BAHAN DASAR BANNER (TANPA FINISHING / id_pilihan_finishing = null)
            // // ------------------------------------------------------------------------------
            // // --- 60x160 cm (Luas: 0.96 m2) ---
            // ['id_sku' => 'PRD-3001-SKU-001', 'id_bahan_baku' => 'BAHAN-0026', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 21120, 'created_at' => $now, 'updated_at' => $now], // Albatros
            // ['id_sku' => 'PRD-3001-SKU-003', 'id_bahan_baku' => 'BAHAN-0023', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 5760, 'created_at' => $now, 'updated_at' => $now], // Flexi China 280
            // ['id_sku' => 'PRD-3001-SKU-005', 'id_bahan_baku' => 'BAHAN-0025', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 24000, 'created_at' => $now, 'updated_at' => $now], // Flexi Jerman 510
            // ['id_sku' => 'PRD-3001-SKU-007', 'id_bahan_baku' => 'BAHAN-0024', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 14400, 'created_at' => $now, 'updated_at' => $now], // Flexi Korea 440
            // ['id_sku' => 'PRD-3001-SKU-009', 'id_bahan_baku' => 'BAHAN-0027', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 28800, 'created_at' => $now, 'updated_at' => $now], // Luster
            // ['id_sku' => 'PRD-3001-SKU-011', 'id_bahan_baku' => 'BAHAN-0030', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 17280, 'created_at' => $now, 'updated_at' => $now], // Photopaper

            // // --- 80x180 cm (Luas: 1.44 m2) ---
            // ['id_sku' => 'PRD-3001-SKU-002', 'id_bahan_baku' => 'BAHAN-0026', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.44, 'hpp' => 31680, 'created_at' => $now, 'updated_at' => $now], // Albatros
            // ['id_sku' => 'PRD-3001-SKU-004', 'id_bahan_baku' => 'BAHAN-0023', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.44, 'hpp' => 8640, 'created_at' => $now, 'updated_at' => $now], // Flexi China 280
            // ['id_sku' => 'PRD-3001-SKU-006', 'id_bahan_baku' => 'BAHAN-0025', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.44, 'hpp' => 36000, 'created_at' => $now, 'updated_at' => $now], // Flexi Jerman 510
            // ['id_sku' => 'PRD-3001-SKU-008', 'id_bahan_baku' => 'BAHAN-0024', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.44, 'hpp' => 21600, 'created_at' => $now, 'updated_at' => $now], // Flexi Korea 440
            // ['id_sku' => 'PRD-3001-SKU-010', 'id_bahan_baku' => 'BAHAN-0027', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.44, 'hpp' => 43200, 'created_at' => $now, 'updated_at' => $now], // Luster
            // ['id_sku' => 'PRD-3001-SKU-012', 'id_bahan_baku' => 'BAHAN-0030', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.44, 'hpp' => 25920, 'created_at' => $now, 'updated_at' => $now], // Photopaper

            // // 2. BAHAN FINISHING: LAMINASI (Sesuai list lu: SKU-001 & SKU-002)
            // // ------------------------------------------------------------------------------
            // // --- SKU-001 (60x160 cm - 0.96 m2) ---
            // ['id_sku' => 'PRD-3001-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.96, 'hpp' => 9120, 'created_at' => $now, 'updated_at' => $now], // Laminasi Doff
            // ['id_sku' => 'PRD-3001-SKU-001', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.96, 'hpp' => 7680, 'created_at' => $now, 'updated_at' => $now], // Laminasi Glossy
            // // --- SKU-002 (80x180 cm - 1.44 m2) ---
            // ['id_sku' => 'PRD-3001-SKU-002', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1.44, 'hpp' => 13680, 'created_at' => $now, 'updated_at' => $now], // Laminasi Doff
            // ['id_sku' => 'PRD-3001-SKU-002', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1.44, 'hpp' => 11520, 'created_at' => $now, 'updated_at' => $now], // Laminasi Glossy

            // // 3. BAHAN FINISHING: KAKI BANNER (Sesuai list lu: SKU-001, SKU-005, SKU-007)
            // // Note: Jangan lupa ganti "FIN-XXX-001" (Pilihan X-Banner) dan "FIN-XXX-002" (Pilihan Y-Banner)
            // // ------------------------------------------------------------------------------
            // // --- SKU-001 (60x160 cm) ---
            // ['id_sku' => 'PRD-3001-SKU-001', 'id_bahan_baku' => 'BAHAN-0038', 'id_pilihan_finishing' => 'FIN-005-001', 'jumlah_pakai' => 1, 'hpp' => 18000, 'created_at' => $now, 'updated_at' => $now], // Rangka X-Banner 60x160 cm
            // ['id_sku' => 'PRD-3001-SKU-001', 'id_bahan_baku' => 'BAHAN-0040', 'id_pilihan_finishing' => 'FIN-005-002', 'jumlah_pakai' => 1, 'hpp' => 45000, 'created_at' => $now, 'updated_at' => $now], // Rangka Y-Banner 60x160 cm
            // // --- SKU-005 (60x160 cm) ---
            // ['id_sku' => 'PRD-3001-SKU-005', 'id_bahan_baku' => 'BAHAN-0038', 'id_pilihan_finishing' => 'FIN-005-001', 'jumlah_pakai' => 1, 'hpp' => 18000, 'created_at' => $now, 'updated_at' => $now], // Rangka X-Banner 60x160 cm
            // ['id_sku' => 'PRD-3001-SKU-005', 'id_bahan_baku' => 'BAHAN-0040', 'id_pilihan_finishing' => 'FIN-005-002', 'jumlah_pakai' => 1, 'hpp' => 45000, 'created_at' => $now, 'updated_at' => $now], // Rangka Y-Banner 60x160 cm
            // // --- SKU-007 (60x160 cm) ---
            // ['id_sku' => 'PRD-3001-SKU-007', 'id_bahan_baku' => 'BAHAN-0038', 'id_pilihan_finishing' => 'FIN-005-001', 'jumlah_pakai' => 1, 'hpp' => 18000, 'created_at' => $now, 'updated_at' => $now], // Rangka X-Banner 60x160 cm
            // ['id_sku' => 'PRD-3001-SKU-007', 'id_bahan_baku' => 'BAHAN-0040', 'id_pilihan_finishing' => 'FIN-005-002', 'jumlah_pakai' => 1, 'hpp' => 45000, 'created_at' => $now, 'updated_at' => $now], // Rangka Y-Banner 60x160 cm

            // // ==============================================================================
            // // KOMPOSISI: PRD-3002 (ROLL BANNER)
            // // ==============================================================================

            // // --- SKU-001 (Albatros 60x160 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-001', 'id_bahan_baku' => 'BAHAN-0026', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 21120, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-001', 'id_bahan_baku' => 'BAHAN-0042', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 95000, 'created_at' => $now, 'updated_at' => $now],    // Stand

            // // --- SKU-002 (Albatros 85x200 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-002', 'id_bahan_baku' => 'BAHAN-0026', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.7, 'hpp' => 37400, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-002', 'id_bahan_baku' => 'BAHAN-0043', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 135000, 'created_at' => $now, 'updated_at' => $now],  // Stand

            // // --- SKU-003 (Flexi China 280 60x160 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-003', 'id_bahan_baku' => 'BAHAN-0023', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 5760, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-003', 'id_bahan_baku' => 'BAHAN-0042', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 95000, 'created_at' => $now, 'updated_at' => $now],   // Stand

            // // --- SKU-004 (Flexi China 280 85x200 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-004', 'id_bahan_baku' => 'BAHAN-0023', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.7, 'hpp' => 10200, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-004', 'id_bahan_baku' => 'BAHAN-0043', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 135000, 'created_at' => $now, 'updated_at' => $now],  // Stand

            // // --- SKU-005 (Flexi Jerman 510 60x160 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-005', 'id_bahan_baku' => 'BAHAN-0025', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 24000, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-005', 'id_bahan_baku' => 'BAHAN-0042', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 95000, 'created_at' => $now, 'updated_at' => $now],   // Stand

            // // --- SKU-006 (Flexi Jerman 510 85x200 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-006', 'id_bahan_baku' => 'BAHAN-0025', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.7, 'hpp' => 42500, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-006', 'id_bahan_baku' => 'BAHAN-0043', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 135000, 'created_at' => $now, 'updated_at' => $now],  // Stand

            // // --- SKU-007 (Flexi Korea 440 60x160 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-007', 'id_bahan_baku' => 'BAHAN-0024', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 14400, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-007', 'id_bahan_baku' => 'BAHAN-0042', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 95000, 'created_at' => $now, 'updated_at' => $now],   // Stand

            // // --- SKU-008 (Flexi Korea 440 85x200 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-008', 'id_bahan_baku' => 'BAHAN-0024', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.7, 'hpp' => 25500, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-008', 'id_bahan_baku' => 'BAHAN-0043', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 135000, 'created_at' => $now, 'updated_at' => $now],  // Stand

            // // --- SKU-009 (Luster 60x160 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-009', 'id_bahan_baku' => 'BAHAN-0027', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 28800, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-009', 'id_bahan_baku' => 'BAHAN-0042', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 95000, 'created_at' => $now, 'updated_at' => $now],   // Stand

            // // --- SKU-010 (Luster 85x200 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-010', 'id_bahan_baku' => 'BAHAN-0027', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.7, 'hpp' => 51000, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-010', 'id_bahan_baku' => 'BAHAN-0043', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 135000, 'created_at' => $now, 'updated_at' => $now],  // Stand

            // // --- SKU-011 (Photopaper 60x160 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-011', 'id_bahan_baku' => 'BAHAN-0030', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 17280, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-011', 'id_bahan_baku' => 'BAHAN-0042', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 95000, 'created_at' => $now, 'updated_at' => $now],   // Stand

            // // --- SKU-012 (Photopaper 85x200 cm) ---
            // ['id_sku' => 'PRD-3002-SKU-012', 'id_bahan_baku' => 'BAHAN-0030', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.7, 'hpp' => 30600, 'created_at' => $now, 'updated_at' => $now], // Cetak
            // ['id_sku' => 'PRD-3002-SKU-012', 'id_bahan_baku' => 'BAHAN-0043', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 135000, 'created_at' => $now, 'updated_at' => $now],  // Stand
            // 3. BAHAN FINISHING: LAMINASI ROLL BANNER (Doff & Glossy)
            // ------------------------------------------------------------------------------

            // // --- SKU-001 (Albatros 60x160 cm - 0.96 m2) ---
            // ['id_sku' => 'PRD-3002-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.96, 'hpp' => 9120, 'created_at' => $now, 'updated_at' => $now], // Laminasi Doff
            // ['id_sku' => 'PRD-3002-SKU-001', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.96, 'hpp' => 7680, 'created_at' => $now, 'updated_at' => $now], // Laminasi Glossy

            // // --- SKU-002 (Albatros 85x200 cm - 1.7 m2) ---
            // ['id_sku' => 'PRD-3002-SKU-002', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1.7, 'hpp' => 16150, 'created_at' => $now, 'updated_at' => $now], // Laminasi Doff
            // ['id_sku' => 'PRD-3002-SKU-002', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1.7, 'hpp' => 13600, 'created_at' => $now, 'updated_at' => $now], // Laminasi Glossy

            // // --- SKU-011 (Photopaper 60x160 cm - 0.96 m2) ---
            // ['id_sku' => 'PRD-3002-SKU-011', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.96, 'hpp' => 9120, 'created_at' => $now, 'updated_at' => $now], // Laminasi Doff
            // ['id_sku' => 'PRD-3002-SKU-011', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.96, 'hpp' => 7680, 'created_at' => $now, 'updated_at' => $now], // Laminasi Glossy

            // // --- SKU-012 (Photopaper 85x200 cm - 1.7 m2) ---
            // ['id_sku' => 'PRD-3002-SKU-012', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 1.7, 'hpp' => 16150, 'created_at' => $now, 'updated_at' => $now], // Laminasi Doff
            // ['id_sku' => 'PRD-3002-SKU-012', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 1.7, 'hpp' => 13600, 'created_at' => $now, 'updated_at' => $now], // Laminasi Glossy
            // // ==============================================================================
            // // KOMPOSISI: PRD-3003 (BACKDROP BACKWALL)
            // // ==============================================================================

            // // --- SKU-001 (Backwall 3x3 m) | Luas Cetak: 7.91 m2 ---
            // // Cetak Stiker Vinyl
            // ['id_sku' => 'PRD-3003-SKU-001', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 7.91, 'hpp' => 158200, 'created_at' => $now, 'updated_at' => $now],
            // // Stand / Rangka Module 3x3
            // ['id_sku' => 'PRD-3003-SKU-001', 'id_bahan_baku' => 'BAHAN-0044', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 1800000, 'created_at' => $now, 'updated_at' => $now],
            // // Laminasi Doff Meteran (BAHAN-0037 = Rp 9.500/m2) -> 7.91 * 9500 = 75145
            // ['id_sku' => 'PRD-3003-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 7.91, 'hpp' => 75145, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-002 (Backwall 3x4 m) | Luas Cetak: 9.52 m2 ---
            // // Cetak Stiker Vinyl
            // ['id_sku' => 'PRD-3003-SKU-002', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 9.52, 'hpp' => 190400, 'created_at' => $now, 'updated_at' => $now],
            // // Stand / Rangka Module 3x4
            // ['id_sku' => 'PRD-3003-SKU-002', 'id_bahan_baku' => 'BAHAN-0045', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 2200000, 'created_at' => $now, 'updated_at' => $now],
            // // Laminasi Doff Meteran (BAHAN-0037 = Rp 9.500/m2) -> 9.52 * 9500 = 90440
            // ['id_sku' => 'PRD-3003-SKU-002', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 9.52, 'hpp' => 90440, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-3004 (TENT CARD AKRILIK) - REVISI ART PAPER 120 GSM
            // // ==============================================================================

            // // --- SKU-001 (Tent Card Akrilik A4) ---
            // // 1. Akrilik Holder A4
            // ['id_sku' => 'PRD-3004-SKU-001', 'id_bahan_baku' => 'BAHAN-0047', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 25000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Kertas Insert (Art Paper 120 Gsm) -> 0.5 Lembar A3+ (HPP: 0.5 * 300 = 150)
            // ['id_sku' => 'PRD-3004-SKU-001', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 150, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-002 (Tent Card Akrilik A5) ---
            // // 1. Akrilik Holder A5
            // ['id_sku' => 'PRD-3004-SKU-002', 'id_bahan_baku' => 'BAHAN-0048', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 15000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Kertas Insert (Art Paper 120 Gsm) -> 0.25 Lembar A3+ (HPP: 0.25 * 300 = 75)
            // ['id_sku' => 'PRD-3004-SKU-002', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 75, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-003 (Tent Card Akrilik A6) ---
            // // 1. Akrilik Holder A6
            // ['id_sku' => 'PRD-3004-SKU-003', 'id_bahan_baku' => 'BAHAN-0049', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 10000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Kertas Insert (Art Paper 120 Gsm) -> 0.125 Lembar A3+ (HPP: 0.125 * 300 = 38)
            // ['id_sku' => 'PRD-3004-SKU-003', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.125, 'hpp' => 38, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-3005 (WOBLER)
            // // ==============================================================================

            // // --- SKU-001 (Wobler Mika 12cm) ---
            // // 1. Kertas Art Carton 310 Gsm (BAHAN-0008) -> 1 lembar A3+ muat 8 pcs = 0.125 lembar (HPP = 0.125 * 1050 = 131)
            // ['id_sku' => 'PRD-3005-SKU-001', 'id_bahan_baku' => 'BAHAN-0008', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.125, 'hpp' => 131, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Tangkai Mika 3mm (1 Pcs)
            // ['id_sku' => 'PRD-3005-SKU-001', 'id_bahan_baku' => 'BAHAN-0050', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],


            // // ==============================================================================
            // // KOMPOSISI: PRD-3006 (POP UP TABLE)
            // // ==============================================================================

            // // --- SKU-001 (Pop Up Table) ---
            // // 1. Rangka Meja Pop Up Set (BAHAN-0051)
            // ['id_sku' => 'PRD-3006-SKU-001', 'id_bahan_baku' => 'BAHAN-0051', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 1100000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl Meteran (BAHAN-0046) -> Luas 1.8576 m2 (HPP = 1.8576 * 20000 = 37152)
            // ['id_sku' => 'PRD-3006-SKU-001', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.8576, 'hpp' => 37152, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Laminasi Doff Meteran (BAHAN-0037) biar rapi -> Luas 1.8576 m2 (HPP = 1.8576 * 9500 = 17647)
            // ['id_sku' => 'PRD-3006-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.8576, 'hpp' => 17647, 'created_at' => $now, 'updated_at' => $now],


            // // ==============================================================================
            // // KOMPOSISI: PRD-3007 (EVENT DESK / MEJA PROMOSI)
            // // ==============================================================================

            // // --- SKU-001 (Event Desk) ---
            // // 1. Rangka Event Desk PVC Set (BAHAN-0052)
            // ['id_sku' => 'PRD-3007-SKU-001', 'id_bahan_baku' => 'BAHAN-0052', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 550000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl Meteran (BAHAN-0046) -> Luas 1.656 m2 (HPP = 1.656 * 20000 = 33120)
            // ['id_sku' => 'PRD-3007-SKU-001', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.656, 'hpp' => 33120, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Laminasi Doff Meteran (BAHAN-0037) biar tahan gesekan -> Luas 1.656 m2 (HPP = 1.656 * 9500 = 15732)
            // ['id_sku' => 'PRD-3007-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1.656, 'hpp' => 15732, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-3008 (HUMAN STANDEE)
            // // ==============================================================================

            // // --- SKU-001 (Human Standee 60x160 cm) | Luas: 0.96 m2 ---
            // // 1. Papan Impraboard 5mm (HPP = 0.96 * 50000 = 48000)
            // ['id_sku' => 'PRD-3008-SKU-001', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 48000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (HPP = 0.96 * 20000 = 19200)
            // ['id_sku' => 'PRD-3008-SKU-001', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 19200, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Laminasi Doff (HPP = 0.96 * 9500 = 9120)
            // ['id_sku' => 'PRD-3008-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.96, 'hpp' => 9120, 'created_at' => $now, 'updated_at' => $now],
            // // 4. Kaki Standee Besi (BAHAN-0054)
            // ['id_sku' => 'PRD-3008-SKU-001', 'id_bahan_baku' => 'BAHAN-0054', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 65000, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-002 (Human Standee A1) | Luas: 0.5 m2 ---
            // // 1. Papan Impraboard 5mm (HPP = 0.5 * 50000 = 25000)
            // ['id_sku' => 'PRD-3008-SKU-002', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 25000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (HPP = 0.5 * 20000 = 10000)
            // ['id_sku' => 'PRD-3008-SKU-002', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 10000, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Laminasi Doff (HPP = 0.5 * 9500 = 4750)
            // ['id_sku' => 'PRD-3008-SKU-002', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 4750, 'created_at' => $now, 'updated_at' => $now],
            // // 4. Kaki Standee Besi (BAHAN-0054)
            // ['id_sku' => 'PRD-3008-SKU-002', 'id_bahan_baku' => 'BAHAN-0054', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 65000, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-003 (Human Standee A2) | Luas: 0.25 m2 ---
            // // 1. Papan Impraboard 5mm (HPP = 0.25 * 50000 = 12500)
            // ['id_sku' => 'PRD-3008-SKU-003', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 12500, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (HPP = 0.25 * 20000 = 5000)
            // ['id_sku' => 'PRD-3008-SKU-003', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 5000, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Laminasi Doff (HPP = 0.25 * 9500 = 2375)
            // ['id_sku' => 'PRD-3008-SKU-003', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 2375, 'created_at' => $now, 'updated_at' => $now],
            // // 4. Kaki Standee Mini Board (BAHAN-0055)
            // ['id_sku' => 'PRD-3008-SKU-003', 'id_bahan_baku' => 'BAHAN-0055', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 15000, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-004 (Human Standee A3) | Luas: 0.125 m2 ---
            // // 1. Papan Impraboard 5mm (HPP = 0.125 * 50000 = 6250)
            // ['id_sku' => 'PRD-3008-SKU-004', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.125, 'hpp' => 6250, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (HPP = 0.125 * 20000 = 2500)
            // ['id_sku' => 'PRD-3008-SKU-004', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.125, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Laminasi Doff (HPP = 0.125 * 9500 = 1187.5 ~ 1188)
            // ['id_sku' => 'PRD-3008-SKU-004', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.125, 'hpp' => 1188, 'created_at' => $now, 'updated_at' => $now],
            // // 4. Kaki Standee Mini Board (BAHAN-0055)
            // ['id_sku' => 'PRD-3008-SKU-004', 'id_bahan_baku' => 'BAHAN-0055', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 15000, 'created_at' => $now, 'updated_at' => $now],
            // // ==============================================================================
            // // KOMPOSISI: PRD-3009 (TRIPOD BANNER)
            // // ==============================================================================

            // // --- SKU-001 (Impraboard A1 - TANPA TRIPOD) | Luas: 0.48 m2 ---
            // // 1. Papan Impraboard 5mm (BAHAN-0053) -> HPP = 0.48 * 50000 = 24000
            // ['id_sku' => 'PRD-3009-SKU-001', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.48, 'hpp' => 24000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (BAHAN-0046) -> HPP = 0.48 * 20000 = 9600
            // ['id_sku' => 'PRD-3009-SKU-001', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.48, 'hpp' => 9600, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-002 (Impraboard A2 - TANPA TRIPOD) | Luas: 0.24 m2 ---
            // // 1. Papan Impraboard 5mm (BAHAN-0053) -> HPP = 0.24 * 50000 = 12000
            // ['id_sku' => 'PRD-3009-SKU-002', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.24, 'hpp' => 12000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (BAHAN-0046) -> HPP = 0.24 * 20000 = 4800
            // ['id_sku' => 'PRD-3009-SKU-002', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.24, 'hpp' => 4800, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-003 (Impraboard A3 - TANPA TRIPOD) | Luas: 0.12 m2 ---
            // // 1. Papan Impraboard 5mm (BAHAN-0053) -> HPP = 0.12 * 50000 = 6000
            // ['id_sku' => 'PRD-3009-SKU-003', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.12, 'hpp' => 6000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (BAHAN-0046) -> HPP = 0.12 * 20000 = 2400
            // ['id_sku' => 'PRD-3009-SKU-003', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.12, 'hpp' => 2400, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-004 (Tripod 60x120 cm - KOMPLIT) | Luas: 0.72 m2 ---
            // // 1. Papan Impraboard 5mm (BAHAN-0053) -> HPP = 0.72 * 50000 = 36000
            // ['id_sku' => 'PRD-3009-SKU-004', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.72, 'hpp' => 36000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (BAHAN-0046) -> HPP = 0.72 * 20000 = 14400
            // ['id_sku' => 'PRD-3009-SKU-004', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.72, 'hpp' => 14400, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Kaki Tripod Banner (BAHAN-0056)
            // ['id_sku' => 'PRD-3009-SKU-004', 'id_bahan_baku' => 'BAHAN-0056', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 45000, 'created_at' => $now, 'updated_at' => $now],
            // // 4A. Pilihan Laminasi Doff (BAHAN-0037) -> HPP = 0.72 * 9500 = 6840
            // ['id_sku' => 'PRD-3009-SKU-004', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.72, 'hpp' => 6840, 'created_at' => $now, 'updated_at' => $now],
            // // 4B. Pilihan Laminasi Glossy (BAHAN-0036) -> HPP = 0.72 * 8000 = 5760
            // ['id_sku' => 'PRD-3009-SKU-004', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.72, 'hpp' => 5760, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-005 (Tripod A1 - KOMPLIT) | Luas: 0.48 m2 ---
            // // 1. Papan Impraboard 5mm (BAHAN-0053) -> HPP = 0.48 * 50000 = 24000
            // ['id_sku' => 'PRD-3009-SKU-005', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.48, 'hpp' => 24000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (BAHAN-0046) -> HPP = 0.48 * 20000 = 9600
            // ['id_sku' => 'PRD-3009-SKU-005', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.48, 'hpp' => 9600, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Kaki Tripod Banner (BAHAN-0056)
            // ['id_sku' => 'PRD-3009-SKU-005', 'id_bahan_baku' => 'BAHAN-0056', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 45000, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-006 (Tripod A2 - KOMPLIT) | Luas: 0.24 m2 ---
            // // 1. Papan Impraboard 5mm (BAHAN-0053) -> HPP = 0.24 * 50000 = 12000
            // ['id_sku' => 'PRD-3009-SKU-006', 'id_bahan_baku' => 'BAHAN-0053', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.24, 'hpp' => 12000, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Cetak Stiker Vinyl (BAHAN-0046) -> HPP = 0.24 * 20000 = 4800
            // ['id_sku' => 'PRD-3009-SKU-006', 'id_bahan_baku' => 'BAHAN-0046', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.24, 'hpp' => 4800, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Kaki Tripod Banner (BAHAN-0056)
            // ['id_sku' => 'PRD-3009-SKU-006', 'id_bahan_baku' => 'BAHAN-0056', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 45000, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-3010 (BROSUR FLYER) - 1 RIM (500 LEMBAR)
            // // ==============================================================================

            // // --- SKU-001 (Brosur A3 - 1 Rim) ---
            // // Art Paper 120 Gsm (BAHAN-0004) -> Pemakaian: 500 Lembar A3+
            // ['id_sku' => 'PRD-3010-SKU-001', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 500, 'hpp' => 150000, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-002 (Brosur A4 - 1 Rim) ---
            // // Art Paper 120 Gsm (BAHAN-0004) -> Pemakaian: 250 Lembar A3+
            // ['id_sku' => 'PRD-3010-SKU-002', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 250, 'hpp' => 75000, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-003 (Brosur A5 - 1 Rim) ---
            // // Art Paper 120 Gsm (BAHAN-0004) -> Pemakaian: 125 Lembar A3+
            // ['id_sku' => 'PRD-3010-SKU-003', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 125, 'hpp' => 37500, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-004 (Brosur A6 - 1 Rim) ---
            // // Art Paper 120 Gsm (BAHAN-0004) -> Pemakaian: 62.5 Lembar A3+
            // ['id_sku' => 'PRD-3010-SKU-004', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 62.5, 'hpp' => 18750, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-005 (Brosur 1/3 A4 - 1 Rim) ---
            // // Art Paper 120 Gsm (BAHAN-0004) -> Pemakaian: 83.33 Lembar A3+
            // ['id_sku' => 'PRD-3010-SKU-005', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 83.33, 'hpp' => 25000, 'created_at' => $now, 'updated_at' => $now],
            // // ==============================================================================
            // // KOMPOSISI: PRD-4001 (KALENDER MEJA) - Dihitung per 1 Pcs
            // // ==============================================================================

            // // --- SKU-001 (AC 210 Gsm - 8 Lembar) ---
            // // 1. Art Carton 210 Gsm (BAHAN-0006) -> Pemakaian: 2 Lembar A3+ -> HPP: 2 * 700 = 1400
            // ['id_sku' => 'PRD-4001-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 2, 'hpp' => 1400, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Hardcover Linen Hitam (BAHAN-0057)
            // ['id_sku' => 'PRD-4001-SKU-001', 'id_bahan_baku' => 'BAHAN-0057', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Spiral Kawat (BAHAN-0058)
            // ['id_sku' => 'PRD-4001-SKU-001', 'id_bahan_baku' => 'BAHAN-0058', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // // 4A. Pilihan Laminasi Doff (HPP: 0.6144 * 9500 = 5837)
            // ['id_sku' => 'PRD-4001-SKU-001', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.6144, 'hpp' => 5837, 'created_at' => $now, 'updated_at' => $now],
            // // 4B. Pilihan Laminasi Glossy (HPP: 0.6144 * 8000 = 4915)
            // ['id_sku' => 'PRD-4001-SKU-001', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.6144, 'hpp' => 4915, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-002 (AC 210 Gsm - 13 Lembar) ---
            // // 1. Art Carton 210 Gsm (BAHAN-0006) -> Pemakaian: 3.25 Lembar A3+ -> HPP: 3.25 * 700 = 2275
            // ['id_sku' => 'PRD-4001-SKU-002', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 3.25, 'hpp' => 2275, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Hardcover Linen Hitam (BAHAN-0057)
            // ['id_sku' => 'PRD-4001-SKU-002', 'id_bahan_baku' => 'BAHAN-0057', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Spiral Kawat (BAHAN-0058)
            // ['id_sku' => 'PRD-4001-SKU-002', 'id_bahan_baku' => 'BAHAN-0058', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // // 4A. Pilihan Laminasi Doff (HPP: 0.9984 * 9500 = 9485)
            // ['id_sku' => 'PRD-4001-SKU-002', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.9984, 'hpp' => 9485, 'created_at' => $now, 'updated_at' => $now],
            // // 4B. Pilihan Laminasi Glossy (HPP: 0.9984 * 8000 = 7987)
            // ['id_sku' => 'PRD-4001-SKU-002', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.9984, 'hpp' => 7987, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-003 (AC 260 Gsm - 8 Lembar) ---
            // // 1. Art Carton 260 Gsm (BAHAN-0007) -> Pemakaian: 2 Lembar A3+ -> HPP: 2 * 850 = 1700
            // ['id_sku' => 'PRD-4001-SKU-003', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 2, 'hpp' => 1700, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Hardcover Linen Hitam (BAHAN-0057)
            // ['id_sku' => 'PRD-4001-SKU-003', 'id_bahan_baku' => 'BAHAN-0057', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Spiral Kawat (BAHAN-0058)
            // ['id_sku' => 'PRD-4001-SKU-003', 'id_bahan_baku' => 'BAHAN-0058', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // // 4A. Pilihan Laminasi Doff (HPP: 0.6144 * 9500 = 5837)
            // ['id_sku' => 'PRD-4001-SKU-003', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.6144, 'hpp' => 5837, 'created_at' => $now, 'updated_at' => $now],
            // // 4B. Pilihan Laminasi Glossy (HPP: 0.6144 * 8000 = 4915)
            // ['id_sku' => 'PRD-4001-SKU-003', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.6144, 'hpp' => 4915, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-004 (AC 260 Gsm - 13 Lembar) ---
            // // 1. Art Carton 260 Gsm (BAHAN-0007) -> Pemakaian: 3.25 Lembar A3+ -> HPP: 3.25 * 850 = 2762.5 ~ 2763
            // ['id_sku' => 'PRD-4001-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 3.25, 'hpp' => 2763, 'created_at' => $now, 'updated_at' => $now],
            // // 2. Hardcover Linen Hitam (BAHAN-0057)
            // ['id_sku' => 'PRD-4001-SKU-004', 'id_bahan_baku' => 'BAHAN-0057', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],
            // // 3. Spiral Kawat (BAHAN-0058)
            // ['id_sku' => 'PRD-4001-SKU-004', 'id_bahan_baku' => 'BAHAN-0058', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // // 4A. Pilihan Laminasi Doff (HPP: 0.9984 * 9500 = 9485)
            // ['id_sku' => 'PRD-4001-SKU-004', 'id_bahan_baku' => 'BAHAN-0037', 'id_pilihan_finishing' => 'FIN-001-001', 'jumlah_pakai' => 0.9984, 'hpp' => 9485, 'created_at' => $now, 'updated_at' => $now],
            // // 4B. Pilihan Laminasi Glossy (HPP: 0.9984 * 8000 = 7987)
            // ['id_sku' => 'PRD-4001-SKU-004', 'id_bahan_baku' => 'BAHAN-0036', 'id_pilihan_finishing' => 'FIN-001-002', 'jumlah_pakai' => 0.9984, 'hpp' => 7987, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-4002 (KALENDER DINDING)
            // // ==============================================================================

            // // --- SKU-001 (Art Carton 210 Gsm - BAHAN-0006 @ Rp 700) ---
            // // Mapping Jumlah Lembar Kertas
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 1, 'hpp' => 700, 'created_at' => $now, 'updated_at' => $now], // 1 Lembar
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 2, 'hpp' => 1400, 'created_at' => $now, 'updated_at' => $now], // 2 Lembar
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 3, 'hpp' => 2100, 'created_at' => $now, 'updated_at' => $now], // 3 Lembar
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 4, 'hpp' => 2800, 'created_at' => $now, 'updated_at' => $now], // 4 Lembar
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 5, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now], // 5 Lembar (4+1 Cover)
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 6, 'hpp' => 4200, 'created_at' => $now, 'updated_at' => $now], // 6 Lembar
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 7, 'hpp' => 4900, 'created_at' => $now, 'updated_at' => $now], // 7 Lembar (6+1 Cover)
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 12, 'hpp' => 8400, 'created_at' => $now, 'updated_at' => $now], // 12 Lembar
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0006', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 13, 'hpp' => 9100, 'created_at' => $now, 'updated_at' => $now], // 13 Lembar (12+1 Cover)
            // // Mapping Gantungan
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0059', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1000, 'created_at' => $now, 'updated_at' => $now], // Klepseng
            // ['id_sku' => 'PRD-4002-SKU-001', 'id_bahan_baku' => 'BAHAN-0060', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now], // Spiral


            // // --- SKU-004 (Art Carton 260 Gsm - BAHAN-0007 @ Rp 850) ---
            // // Mapping Jumlah Lembar Kertas
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 1, 'hpp' => 850, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 2, 'hpp' => 1700, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 3, 'hpp' => 2550, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 4, 'hpp' => 3400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 5, 'hpp' => 4250, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 6, 'hpp' => 5100, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 7, 'hpp' => 5950, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 12, 'hpp' => 10200, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0007', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 13, 'hpp' => 11050, 'created_at' => $now, 'updated_at' => $now],
            // // Mapping Gantungan
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0059', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-004', 'id_bahan_baku' => 'BAHAN-0060', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-007 (Art Paper 120 Gsm - BAHAN-0004 @ Rp 450) ---
            // // Mapping Jumlah Lembar Kertas
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 1, 'hpp' => 450, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 2, 'hpp' => 900, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 3, 'hpp' => 1350, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 4, 'hpp' => 1800, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 5, 'hpp' => 2250, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 6, 'hpp' => 2700, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 7, 'hpp' => 3150, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 12, 'hpp' => 5400, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0004', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 13, 'hpp' => 5850, 'created_at' => $now, 'updated_at' => $now],
            // // Mapping Gantungan
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0059', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-007', 'id_bahan_baku' => 'BAHAN-0060', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-010 (Art Paper 150 Gsm - BAHAN-0005 @ Rp 550) ---
            // // Mapping Jumlah Lembar Kertas
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 1, 'hpp' => 550, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 2, 'hpp' => 1100, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 3, 'hpp' => 1650, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 4, 'hpp' => 2200, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 5, 'hpp' => 2750, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 6, 'hpp' => 3300, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 7, 'hpp' => 3850, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 12, 'hpp' => 6600, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0005', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 13, 'hpp' => 7150, 'created_at' => $now, 'updated_at' => $now],
            // // Mapping Gantungan
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0059', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1000, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-010', 'id_bahan_baku' => 'BAHAN-0060', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 2500, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-4002 (KALENDER DINDING 38x53)
            // // ==============================================================================

            // // --- SKU-002 (Art Carton 210 Gsm 38x53 - BAHAN-0062 @ Rp 10.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2014, 'hpp' => 2014, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.4028, 'hpp' => 4028, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.6042, 'hpp' => 6042, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 0.8056, 'hpp' => 8056, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.0070, 'hpp' => 10070, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.2084, 'hpp' => 12084, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 1.4098, 'hpp' => 14098, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 2.4168, 'hpp' => 24168, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 2.6182, 'hpp' => 26182, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 38cm
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0065', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1200, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-002', 'id_bahan_baku' => 'BAHAN-0066', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3000, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-005 (Art Carton 260 Gsm 38x53 - BAHAN-0063 @ Rp 11.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2014, 'hpp' => 2215, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.4028, 'hpp' => 4431, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.6042, 'hpp' => 6646, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 0.8056, 'hpp' => 8862, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.0070, 'hpp' => 11077, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.2084, 'hpp' => 13292, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 1.4098, 'hpp' => 15508, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 2.4168, 'hpp' => 26585, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 2.6182, 'hpp' => 28800, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 38cm
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0065', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1200, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-005', 'id_bahan_baku' => 'BAHAN-0066', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3000, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-008 (Art Paper 120 Gsm 38x53 - BAHAN-0061 @ Rp 7.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2014, 'hpp' => 1410, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.4028, 'hpp' => 2820, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.6042, 'hpp' => 4229, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 0.8056, 'hpp' => 5639, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.0070, 'hpp' => 7049, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.2084, 'hpp' => 8459, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 1.4098, 'hpp' => 9869, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 2.4168, 'hpp' => 16918, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 2.6182, 'hpp' => 18327, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 38cm
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0065', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1200, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-008', 'id_bahan_baku' => 'BAHAN-0066', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3000, 'created_at' => $now, 'updated_at' => $now],

            // // --- SKU-011 (Art Paper 150 Gsm 38x53 - BAHAN-0031 (Master Lama) @ Rp 8.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2014, 'hpp' => 1611, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.4028, 'hpp' => 3222, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.6042, 'hpp' => 4834, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 0.8056, 'hpp' => 6445, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.0070, 'hpp' => 8056, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.2084, 'hpp' => 9667, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 1.4098, 'hpp' => 11278, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 2.4168, 'hpp' => 19334, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 2.6182, 'hpp' => 20946, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 38cm
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0065', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1200, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-011', 'id_bahan_baku' => 'BAHAN-0066', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3000, 'created_at' => $now, 'updated_at' => $now],

            // // ==============================================================================
            // // KOMPOSISI: PRD-4002 (KALENDER DINDING 46x64)
            // // ==============================================================================

            // // --- SKU-003 (Art Carton 210 Gsm 46x64 - BAHAN-0062 @ Rp 10.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2944, 'hpp' => 2944, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.5888, 'hpp' => 5888, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.8832, 'hpp' => 8832, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 1.1776, 'hpp' => 11776, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.4720, 'hpp' => 14720, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.7664, 'hpp' => 17664, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 2.0608, 'hpp' => 20608, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 3.5328, 'hpp' => 35328, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0062', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 3.8272, 'hpp' => 38272, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 46cm
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0067', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-003', 'id_bahan_baku' => 'BAHAN-0068', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-006 (Art Carton 260 Gsm 46x64 - BAHAN-0063 @ Rp 11.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2944, 'hpp' => 3238, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.5888, 'hpp' => 6477, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.8832, 'hpp' => 9715, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 1.1776, 'hpp' => 12954, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.4720, 'hpp' => 16192, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.7664, 'hpp' => 19430, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 2.0608, 'hpp' => 22669, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 3.5328, 'hpp' => 38861, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0063', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 3.8272, 'hpp' => 42099, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 46cm
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0067', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-006', 'id_bahan_baku' => 'BAHAN-0068', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-009 (Art Paper 120 Gsm 46x64 - BAHAN-0061 @ Rp 7.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2944, 'hpp' => 2061, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.5888, 'hpp' => 4122, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.8832, 'hpp' => 6182, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 1.1776, 'hpp' => 8243, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.4720, 'hpp' => 10304, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.7664, 'hpp' => 12365, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 2.0608, 'hpp' => 14426, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 3.5328, 'hpp' => 24730, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0061', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 3.8272, 'hpp' => 26790, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 46cm
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0067', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-009', 'id_bahan_baku' => 'BAHAN-0068', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],


            // // --- SKU-012 (Art Paper 150 Gsm 46x64 - BAHAN-0031 (Master Lama) @ Rp 8.000 / m2) ---
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-001', 'jumlah_pakai' => 0.2944, 'hpp' => 2355, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-002', 'jumlah_pakai' => 0.5888, 'hpp' => 4710, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-003', 'jumlah_pakai' => 0.8832, 'hpp' => 7066, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-004', 'jumlah_pakai' => 1.1776, 'hpp' => 9421, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-005', 'jumlah_pakai' => 1.4720, 'hpp' => 11776, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-006', 'jumlah_pakai' => 1.7664, 'hpp' => 14131, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-007', 'jumlah_pakai' => 2.0608, 'hpp' => 16486, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-008', 'jumlah_pakai' => 3.5328, 'hpp' => 28262, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0031', 'id_pilihan_finishing' => 'FIN-010-009', 'jumlah_pakai' => 3.8272, 'hpp' => 30618, 'created_at' => $now, 'updated_at' => $now],
            // // Gantungan 46cm
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0067', 'id_pilihan_finishing' => 'FIN-011-001', 'jumlah_pakai' => 1, 'hpp' => 1500, 'created_at' => $now, 'updated_at' => $now],
            // ['id_sku' => 'PRD-4002-SKU-012', 'id_bahan_baku' => 'BAHAN-0068', 'id_pilihan_finishing' => 'FIN-011-002', 'jumlah_pakai' => 1, 'hpp' => 3500, 'created_at' => $now, 'updated_at' => $now],

            // ====================================================================================
            // KOMPOSISI PRODUK & FINISHING
            // ====================================================================================

            // --- SKU-001 (Bendera Umbul Umbul - Kain Satin - BAHAN-0069 @ Rp 15.000 / m2) ---
            // Base Kain (Tanpa Finishing Khusus / Menempel di SKU)
            ['id_sku' => 'PRD-3011-SKU-001', 'id_bahan_baku' => 'BAHAN-0069', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 15000, 'created_at' => $now, 'updated_at' => $now],
            // Finishing Jahit Obras
            ['id_sku' => 'PRD-3011-SKU-001', 'id_bahan_baku' => 'BAHAN-0071', 'id_pilihan_finishing' => 'FIN-017-001', 'jumlah_pakai' => 0.05, 'hpp' => 900, 'created_at' => $now, 'updated_at' => $now],
            // Finishing Jahit Obras & Tali
            ['id_sku' => 'PRD-3011-SKU-001', 'id_bahan_baku' => 'BAHAN-0071', 'id_pilihan_finishing' => 'FIN-017-002', 'jumlah_pakai' => 0.05, 'hpp' => 900, 'created_at' => $now, 'updated_at' => $now],
            ['id_sku' => 'PRD-3011-SKU-001', 'id_bahan_baku' => 'BAHAN-0072', 'id_pilihan_finishing' => 'FIN-017-002', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],

            // --- SKU-002 (Bendera Umbul Umbul - Kain TC - BAHAN-0070 @ Rp 12.000 / m2) ---
            // Base Kain (Tanpa Finishing Khusus / Menempel di SKU)
            ['id_sku' => 'PRD-3011-SKU-002', 'id_bahan_baku' => 'BAHAN-0070', 'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 12000, 'created_at' => $now, 'updated_at' => $now],
            // Finishing Jahit Obras
            ['id_sku' => 'PRD-3011-SKU-002', 'id_bahan_baku' => 'BAHAN-0071', 'id_pilihan_finishing' => 'FIN-017-001', 'jumlah_pakai' => 0.05, 'hpp' => 900, 'created_at' => $now, 'updated_at' => $now],
            // Finishing Jahit Obras & Tali
            ['id_sku' => 'PRD-3011-SKU-002', 'id_bahan_baku' => 'BAHAN-0071', 'id_pilihan_finishing' => 'FIN-017-002', 'jumlah_pakai' => 0.05, 'hpp' => 900, 'created_at' => $now, 'updated_at' => $now],
            ['id_sku' => 'PRD-3011-SKU-002', 'id_bahan_baku' => 'BAHAN-0072', 'id_pilihan_finishing' => 'FIN-017-002', 'jumlah_pakai' => 1, 'hpp' => 500, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
