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
            // ==========================================
            // KOMPOSISI PRINT A0 (Luas 1 m2 -> Pemakaian = 1)
            // ==========================================
            [
                'id_sku' => 'PRD-1001-SKU-001', 'id_bahan_baku' => 'BAHAN-0026', // A0 Albatros
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 22000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1001-SKU-002', 'id_bahan_baku' => 'BAHAN-0026', // A0 Poster Albatros
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 22000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1001-SKU-003', 'id_bahan_baku' => 'BAHAN-0028', // A0 HVS B&W
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 4500, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1001-SKU-004', 'id_bahan_baku' => 'BAHAN-0028', // A0 HVS Warna
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 4500, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1001-SKU-005', 'id_bahan_baku' => 'BAHAN-0029', // A0 Kalkir
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 12000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1001-SKU-006', 'id_bahan_baku' => 'BAHAN-0030', // A0 Poster Photopaper
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 1, 'hpp' => 18000, 'created_at' => $now, 'updated_at' => $now
            ],

            // ==========================================
            // KOMPOSISI PRINT A1 (Luas 0.5 m2 -> Pemakaian = 0.5)
            // ==========================================
            [
                'id_sku' => 'PRD-1002-SKU-001', 'id_bahan_baku' => 'BAHAN-0026', // A1 Poster Albatros
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 11000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1002-SKU-002', 'id_bahan_baku' => 'BAHAN-0028', // A1 HVS B&W
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 2250, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1002-SKU-003', 'id_bahan_baku' => 'BAHAN-0028', // A1 HVS Warna
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 2250, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1002-SKU-004', 'id_bahan_baku' => 'BAHAN-0029', // A1 Kalkir
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 6000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1002-SKU-005', 'id_bahan_baku' => 'BAHAN-0031', // A1 Art Paper 150
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 4000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1002-SKU-006', 'id_bahan_baku' => 'BAHAN-0032', // A1 Art Paper 260
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 5500, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1002-SKU-007', 'id_bahan_baku' => 'BAHAN-0030', // A1 Poster Photopaper
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.5, 'hpp' => 9000, 'created_at' => $now, 'updated_at' => $now
            ],

            // ==========================================
            // KOMPOSISI PRINT A2 (Luas 0.25 m2 -> Pemakaian = 0.25)
            // ==========================================
            [
                'id_sku' => 'PRD-1003-SKU-001', 'id_bahan_baku' => 'BAHAN-0028', // A2 HVS B&W
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 1125, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1003-SKU-002', 'id_bahan_baku' => 'BAHAN-0028', // A2 HVS Warna
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 1125, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1003-SKU-003', 'id_bahan_baku' => 'BAHAN-0026', // A2 Poster Albatros
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 5500, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1003-SKU-004', 'id_bahan_baku' => 'BAHAN-0031', // A2 Poster Art Paper 150
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 2000, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1003-SKU-005', 'id_bahan_baku' => 'BAHAN-0032', // A2 Poster Art Paper 260
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 2750, 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_sku' => 'PRD-1003-SKU-006', 'id_bahan_baku' => 'BAHAN-0030', // A2 Poster Photopaper
                'id_pilihan_finishing' => null, 'jumlah_pakai' => 0.25, 'hpp' => 4500, 'created_at' => $now, 'updated_at' => $now
            ],
        ]);
    }
}
