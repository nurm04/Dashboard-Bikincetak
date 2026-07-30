<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('bahan_baku')->insert([
            [
                'id_bahan_baku' => 'BAHAN-0001',
                'nama_bahan_baku' => 'HVS 80 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 12.61,
                'harga_beli' => 300,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0003',
                'nama_bahan_baku' => 'HVS 100 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 15.60,
                'harga_beli' => 300,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0004',
                'nama_bahan_baku' => 'Art Paper 120 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 18.72, // (120 * 0.156)
                'harga_beli' => 450,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0005',
                'nama_bahan_baku' => 'Art Paper 150 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 23.40, // (150 * 0.156)
                'harga_beli' => 550,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0006',
                'nama_bahan_baku' => 'Art Carton 210 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 32.76, // (210 * 0.156)
                'harga_beli' => 700,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0007',
                'nama_bahan_baku' => 'Art Carton 260 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 40.56, // (260 * 0.156)
                'harga_beli' => 850,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0008',
                'nama_bahan_baku' => 'Art Carton 310 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 48.36, // (310 * 0.156)
                'harga_beli' => 1050,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0009',
                'nama_bahan_baku' => 'Photopaper 210 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 32.76, // (210 * 0.156)
                'harga_beli' => 1200,
                'stok_awal' => 250,
                'stok_sekarang' => 250,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0010',
                'nama_bahan_baku' => 'Photopaper 230 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 35.88, // (230 * 0.156)
                'harga_beli' => 1350,
                'stok_awal' => 250,
                'stok_sekarang' => 250,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0011',
                'nama_bahan_baku' => 'Kalkir 80 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 12.48, // (80 * 0.156)
                'harga_beli' => 1500,
                'stok_awal' => 100,
                'stok_sekarang' => 100,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0012',
                'nama_bahan_baku' => 'Kertas Linen 220 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 34.32, // (220 * 0.156)
                'harga_beli' => 1600,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0013',
                'nama_bahan_baku' => 'Kertas Jasmine 210 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 32.76, // (210 * 0.156)
                'harga_beli' => 1500,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0014',
                'nama_bahan_baku' => 'Kertas Concorde 220 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 34.32, // (220 * 0.156)
                'harga_beli' => 1400,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0015',
                'nama_bahan_baku' => 'Kertas Blueswhite (BW) 250 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 39.00, // (250 * 0.156)
                'harga_beli' => 1300,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0016',
                'nama_bahan_baku' => 'Kertas BC Tik 210 Gsm',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 32.76, // (210 * 0.156)
                'harga_beli' => 1250,
                'stok_awal' => 250,
                'stok_sekarang' => 250,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0017',
                'nama_bahan_baku' => 'Inkjet 100 Gsm Warna',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 15.60, // (100 * 0.156)
                'harga_beli' => 600,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],

            // =======================================================
            // 2. KERTAS STIKER (Satuan: Lembar A3+ | Luas: 0.156 m2)
            // =======================================================
            [
                'id_bahan_baku' => 'BAHAN-0018',
                'nama_bahan_baku' => 'Stiker Chromo',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 18.72, // (Rata-rata 120 Gsm * 0.156)
                'harga_beli' => 850,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0019',
                'nama_bahan_baku' => 'Stiker Vinyl Putih',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 23.40, // (Rata-rata 150 Gsm * 0.156)
                'harga_beli' => 2500,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0020',
                'nama_bahan_baku' => 'Stiker Vinyl Transparan',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 23.40, // (Rata-rata 150 Gsm * 0.156)
                'harga_beli' => 2500,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0021',
                'nama_bahan_baku' => 'Stiker Vinyl Silver',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 23.40, // (Rata-rata 150 Gsm * 0.156)
                'harga_beli' => 3000,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0022',
                'nama_bahan_baku' => 'Stiker HVS',
                'satuan' => 'Lembar A3+',
                'berat_gram_persatuan' => 23.40, // (Kertas atas + backing = +-150 Gsm * 0.156)
                'harga_beli' => 900,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],

            // =======================================================
            // 3. BAHAN BANNER / LARGE FORMAT (Satuan: Meter Persegi / m2)
            // =======================================================
            [
                'id_bahan_baku' => 'BAHAN-0023',
                'nama_bahan_baku' => 'Flexi China 280 Gsm',
                'satuan' => 'Meter Persegi',
                'berat_gram_persatuan' => 280.00, // 280 * 1 m2
                'harga_beli' => 6000,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0024',
                'nama_bahan_baku' => 'Flexi Korea 440 Gsm',
                'satuan' => 'Meter Persegi',
                'berat_gram_persatuan' => 440.00, // 440 * 1 m2
                'harga_beli' => 15000,
                'stok_awal' => 200,
                'stok_sekarang' => 200,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0025',
                'nama_bahan_baku' => 'Flexi Jerman 510 Gsm',
                'satuan' => 'Meter Persegi',
                'berat_gram_persatuan' => 510.00, // 510 * 1 m2
                'harga_beli' => 25000,
                'stok_awal' => 150,
                'stok_sekarang' => 150,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0026',
                'nama_bahan_baku' => 'Kertas Albatros (Roll)',
                'satuan' => 'Meter Persegi',
                'berat_gram_persatuan' => 180.00, // Rata-rata Albatros adalah 180-200 GSM
                'harga_beli' => 22000,
                'stok_awal' => 100,
                'stok_sekarang' => 100,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0027',
                'nama_bahan_baku' => 'Luster / Luster Photo',
                'satuan' => 'Meter Persegi',
                'berat_gram_persatuan' => 230.00, // Rata-rata Luster 230-260 GSM
                'harga_beli' => 30000,
                'stok_awal' => 50,
                'stok_sekarang' => 50,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ]
        ]);
    }
}
