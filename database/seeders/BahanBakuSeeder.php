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
            // [
            //     'id_bahan_baku' => 'BAHAN-0001',
            //     'nama_bahan_baku' => 'HVS 80 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 12.61,
            //     'harga_beli' => 300,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0003',
            //     'nama_bahan_baku' => 'HVS 100 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 15.60,
            //     'harga_beli' => 300,
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0004',
            //     'nama_bahan_baku' => 'Art Paper 120 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 18.72, // (120 * 0.156)
            //     'harga_beli' => 450,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0005',
            //     'nama_bahan_baku' => 'Art Paper 150 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 23.40, // (150 * 0.156)
            //     'harga_beli' => 550,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0006',
            //     'nama_bahan_baku' => 'Art Carton 210 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 32.76, // (210 * 0.156)
            //     'harga_beli' => 700,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0007',
            //     'nama_bahan_baku' => 'Art Carton 260 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 40.56, // (260 * 0.156)
            //     'harga_beli' => 850,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0008',
            //     'nama_bahan_baku' => 'Art Carton 310 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 48.36, // (310 * 0.156)
            //     'harga_beli' => 1050,
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0009',
            //     'nama_bahan_baku' => 'Photopaper 210 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 32.76, // (210 * 0.156)
            //     'harga_beli' => 1200,
            //     'stok_awal' => 250,
            //     'stok_sekarang' => 250,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0010',
            //     'nama_bahan_baku' => 'Photopaper 230 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 35.88, // (230 * 0.156)
            //     'harga_beli' => 1350,
            //     'stok_awal' => 250,
            //     'stok_sekarang' => 250,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0011',
            //     'nama_bahan_baku' => 'Kalkir 80 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 12.48, // (80 * 0.156)
            //     'harga_beli' => 1500,
            //     'stok_awal' => 100,
            //     'stok_sekarang' => 100,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0012',
            //     'nama_bahan_baku' => 'Kertas Linen 220 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 34.32, // (220 * 0.156)
            //     'harga_beli' => 1600,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0013',
            //     'nama_bahan_baku' => 'Kertas Jasmine 210 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 32.76, // (210 * 0.156)
            //     'harga_beli' => 1500,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0014',
            //     'nama_bahan_baku' => 'Kertas Concorde 220 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 34.32, // (220 * 0.156)
            //     'harga_beli' => 1400,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0015',
            //     'nama_bahan_baku' => 'Kertas Blueswhite (BW) 250 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 39.00, // (250 * 0.156)
            //     'harga_beli' => 1300,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0016',
            //     'nama_bahan_baku' => 'Kertas BC Tik 210 Gsm',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 32.76, // (210 * 0.156)
            //     'harga_beli' => 1250,
            //     'stok_awal' => 250,
            //     'stok_sekarang' => 250,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0017',
            //     'nama_bahan_baku' => 'Inkjet 100 Gsm Warna',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 15.60, // (100 * 0.156)
            //     'harga_beli' => 600,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],

            // // =======================================================
            // // 2. KERTAS STIKER (Satuan: Lembar A3+ | Luas: 0.156 m2)
            // // =======================================================
            // [
            //     'id_bahan_baku' => 'BAHAN-0018',
            //     'nama_bahan_baku' => 'Stiker Chromo',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 18.72, // (Rata-rata 120 Gsm * 0.156)
            //     'harga_beli' => 850,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0019',
            //     'nama_bahan_baku' => 'Stiker Vinyl Putih',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 23.40, // (Rata-rata 150 Gsm * 0.156)
            //     'harga_beli' => 2500,
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0020',
            //     'nama_bahan_baku' => 'Stiker Vinyl Transparan',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 23.40, // (Rata-rata 150 Gsm * 0.156)
            //     'harga_beli' => 2500,
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0021',
            //     'nama_bahan_baku' => 'Stiker Vinyl Silver',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 23.40, // (Rata-rata 150 Gsm * 0.156)
            //     'harga_beli' => 3000,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0022',
            //     'nama_bahan_baku' => 'Stiker HVS',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 23.40, // (Kertas atas + backing = +-150 Gsm * 0.156)
            //     'harga_beli' => 900,
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],

            // // =======================================================
            // // 3. BAHAN BANNER / LARGE FORMAT (Satuan: Meter Persegi / m2)
            // // =======================================================
            // [
            //     'id_bahan_baku' => 'BAHAN-0023',
            //     'nama_bahan_baku' => 'Flexi China 280 Gsm',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 280.00, // 280 * 1 m2
            //     'harga_beli' => 6000,
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0024',
            //     'nama_bahan_baku' => 'Flexi Korea 440 Gsm',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 440.00, // 440 * 1 m2
            //     'harga_beli' => 15000,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0025',
            //     'nama_bahan_baku' => 'Flexi Jerman 510 Gsm',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 510.00, // 510 * 1 m2
            //     'harga_beli' => 25000,
            //     'stok_awal' => 150,
            //     'stok_sekarang' => 150,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0026',
            //     'nama_bahan_baku' => 'Kertas Albatros (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 180.00, // Rata-rata Albatros adalah 180-200 GSM
            //     'harga_beli' => 22000,
            //     'stok_awal' => 100,
            //     'stok_sekarang' => 100,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0027',
            //     'nama_bahan_baku' => 'Luster / Luster Photo',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 230.00, // Rata-rata Luster 230-260 GSM
            //     'harga_beli' => 30000,
            //     'stok_awal' => 50,
            //     'stok_sekarang' => 50,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0028',
            //     'nama_bahan_baku' => 'HVS 80 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 80.00, // 80 GSM * 1 m2
            //     'harga_beli' => 4500, // Estimasi HPP per m2
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0029',
            //     'nama_bahan_baku' => 'Kalkir 80 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 80.00, // 80 GSM * 1 m2
            //     'harga_beli' => 12000,
            //     'stok_awal' => 100,
            //     'stok_sekarang' => 100,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0030',
            //     'nama_bahan_baku' => 'Photopaper 260 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 260.00, // 260 GSM * 1 m2
            //     'harga_beli' => 18000,
            //     'stok_awal' => 150,
            //     'stok_sekarang' => 150,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0031',
            //     'nama_bahan_baku' => 'Art Paper 150 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 150.00, // 150 GSM * 1 m2
            //     'harga_beli' => 8000,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0032',
            //     'nama_bahan_baku' => 'Art Paper 260 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 260.00, // 260 GSM * 1 m2
            //     'harga_beli' => 11000,
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0033',
            //     'nama_bahan_baku' => 'Stiker Hologram (Pelangi/3D)',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 23.40, // (Kertas atas + backing = +-150 Gsm * 0.156)
            //     'harga_beli' => 6500, // Harga pasaran grosir per A3+ biasanya sekitar Rp6.000 - Rp7.000
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 4. BAHAN LAMINASI (Finishing Proteksi)
            // // =======================================================

            // // ---> A. LAMINASI LEMBARAN (Untuk Print A3/A4 & Stiker A3+)
            // [
            //     'id_bahan_baku' => 'BAHAN-0034',
            //     'nama_bahan_baku' => 'Laminasi Glossy (A3+)',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 5.00, // Estimasi berat plastik laminasi per lembar A3+
            //     'harga_beli' => 400, // Rata-rata HPP per lembar A3+
            //     'stok_awal' => 2000,
            //     'stok_sekarang' => 2000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0035',
            //     'nama_bahan_baku' => 'Laminasi Doff / Matte (A3+)',
            //     'satuan' => 'Lembar A3+',
            //     'berat_gram_persatuan' => 5.00,
            //     'harga_beli' => 500, // Doff biasanya sedikit lebih mahal dari Glossy
            //     'stok_awal' => 2000,
            //     'stok_sekarang' => 2000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],

            // // ---> B. LAMINASI METERAN (Untuk Print A0-A2, Banner Indoor, Stiker Meteran)
            // [
            //     'id_bahan_baku' => 'BAHAN-0036',
            //     'nama_bahan_baku' => 'Laminasi Glossy (Meteran)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 120.00, // Estimasi berat per meter persegi
            //     'harga_beli' => 8000, // Rata-rata HPP bahan roll per m2
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0037',
            //     'nama_bahan_baku' => 'Laminasi Doff / Matte (Meteran)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 120.00,
            //     'harga_beli' => 9500, // Doff roll biasanya sedikit lebih mahal dari Glossy
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 5. RANGKA DISPLAY / STAND BANNER (Satuan: Set)
            // // =======================================================

            // // ---> A. Rangka X-Banner (Tipe Fiber Hitam Standard)
            // [
            //     'id_bahan_baku' => 'BAHAN-0038',
            //     'nama_bahan_baku' => 'Rangka X-Banner Fiber 60x160 cm',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 500.00, // 0.5 kg
            //     'harga_beli' => 18000,
            //     'stok_awal' => 50,
            //     'stok_sekarang' => 50,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0039',
            //     'nama_bahan_baku' => 'Rangka X-Banner Fiber 80x180 cm',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 800.00, // 0.8 kg
            //     'harga_beli' => 35000,
            //     'stok_awal' => 50,
            //     'stok_sekarang' => 50,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],

            // // ---> B. Rangka Y-Banner (Tipe Besi/Aluminium Kokoh)
            // [
            //     'id_bahan_baku' => 'BAHAN-0040',
            //     'nama_bahan_baku' => 'Rangka Y-Banner Besi 60x160 cm',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 1000.00, // 1 kg
            //     'harga_beli' => 45000,
            //     'stok_awal' => 30,
            //     'stok_sekarang' => 30,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0041',
            //     'nama_bahan_baku' => 'Rangka Y-Banner Besi 80x180 cm',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 1300.00, // 1.3 kg
            //     'harga_beli' => 70000,
            //     'stok_awal' => 30,
            //     'stok_sekarang' => 30,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // ---> C. Rangka Roll Banner (Tipe Aluminium Solid)
            // [
            //     'id_bahan_baku' => 'BAHAN-0042',
            //     'nama_bahan_baku' => 'Rangka Roll Banner Aluminium 60x160 cm',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 2000.00, // Sekitar 2 kg
            //     'harga_beli' => 95000,
            //     'stok_awal' => 20,
            //     'stok_sekarang' => 20,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0043',
            //     'nama_bahan_baku' => 'Rangka Roll Banner Aluminium 85x200 cm',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 2800.00, // Sekitar 2.8 kg
            //     'harga_beli' => 135000,
            //     'stok_awal' => 20,
            //     'stok_sekarang' => 20,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 6. RANGKA & MATERIAL BACKDROP / BACKWALL
            // // =======================================================

            // // ---> Rangka Backwall (Hardware Set Komplit)
            // [
            //     'id_bahan_baku' => 'BAHAN-0044',
            //     'nama_bahan_baku' => 'Rangka Backwall Module 3x3 (Set Komplit)',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 25000.00, // Berat aktual sekitar 25 kg (termasuk tas troli koper)
            //     'harga_beli' => 1800000, // Estimasi HPP Set Rangka
            //     'stok_awal' => 5,
            //     'stok_sekarang' => 5,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0045',
            //     'nama_bahan_baku' => 'Rangka Backwall Module 3x4 (Set Komplit)',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 30000.00, // Berat aktual sekitar 30 kg
            //     'harga_beli' => 2200000,
            //     'stok_awal' => 5,
            //     'stok_sekarang' => 5,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],

            // // ---> Bahan Cetak Backwall
            // [
            //     'id_bahan_baku' => 'BAHAN-0046',
            //     'nama_bahan_baku' => 'Stiker Vinyl / Ritrama (Meteran)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 150.00,
            //     'harga_beli' => 20000, // HPP stiker vinyl roll (large format) per m2
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 7. DISPLAY AKRILIK
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0047',
            //     'nama_bahan_baku' => 'Tent Card Akrilik T-Shape A4',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 300.00, // Estimasi 300 gram
            //     'harga_beli' => 25000,
            //     'stok_awal' => 100,
            //     'stok_sekarang' => 100,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0048',
            //     'nama_bahan_baku' => 'Tent Card Akrilik T-Shape A5',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 150.00, // Estimasi 150 gram
            //     'harga_beli' => 15000,
            //     'stok_awal' => 100,
            //     'stok_sekarang' => 100,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0049',
            //     'nama_bahan_baku' => 'Tent Card Akrilik T-Shape A6',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 100.00, // Estimasi 100 gram
            //     'harga_beli' => 10000,
            //     'stok_awal' => 100,
            //     'stok_sekarang' => 100,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 8. AKSESORIS WOBLER & RANGKA MEJA PROMOSI
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0050',
            //     'nama_bahan_baku' => 'Tangkai Mika Wobler 3mm (14x2 cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 5.00,
            //     'harga_beli' => 500, // Estimasi harga tangkai mika + double tape
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0051',
            //     'nama_bahan_baku' => 'Rangka Pop Up Table (Set Komplit)',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 15000.00, // Sekitar 15kg (Meja kayu + Rangka + Tas)
            //     'harga_beli' => 1100000, // Estimasi HPP Rangka Pop Up Table
            //     'stok_awal' => 5,
            //     'stok_sekarang' => 5,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0052',
            //     'nama_bahan_baku' => 'Rangka Event Desk PVC (Set Komplit)',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 9000.00, // Sekitar 9kg (PVC + Tiang Besi + Tas)
            //     'harga_beli' => 550000, // Estimasi HPP Rangka Event Desk
            //     'stok_awal' => 10,
            //     'stok_sekarang' => 10,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 10. MATERIAL BOARD & KAKI STANDEE
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0053',
            //     'nama_bahan_baku' => 'Papan Impraboard 5mm (Meteran)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 800.00,
            //     'harga_beli' => 50000, // Estimasi harga HPP per m2 (dari lembaran utuh)
            //     'stok_awal' => 50,
            //     'stok_sekarang' => 50,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0054',
            //     'nama_bahan_baku' => 'Kaki Standee Besi (Tinggi 150cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 1500.00,
            //     'harga_beli' => 65000, // Untuk standee ukuran 60x160 & A1
            //     'stok_awal' => 30,
            //     'stok_sekarang' => 30,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0055',
            //     'nama_bahan_baku' => 'Kaki Standee Mini Board (Sayap Meja)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 100.00,
            //     'harga_beli' => 15000, // Untuk standee ukuran A2 & A3
            //     'stok_awal' => 50,
            //     'stok_sekarang' => 50,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 11. KAKI TRIPOD BANNER
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0056',
            //     'nama_bahan_baku' => 'Kaki Tripod Banner (Besi)',
            //     'satuan' => 'Set',
            //     'berat_gram_persatuan' => 1200.00, // Sekitar 1.2 kg
            //     'harga_beli' => 45000, // Estimasi harga HPP Tripod Stand
            //     'stok_awal' => 50,
            //     'stok_sekarang' => 50,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 12. MATERIAL KALENDER MEJA (HARDCOVER & SPIRAL)
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0057',
            //     'nama_bahan_baku' => 'Hardcover Dudukan Kalender (Linen Hitam A5)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 150.00,
            //     'harga_beli' => 3500, // Estimasi board + linen hitam
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0058',
            //     'nama_bahan_baku' => 'Spiral Kawat Kalender',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 20.00,
            //     'harga_beli' => 1500, // Spiral kawat A5
            //     'stok_awal' => 500,
            //     'stok_sekarang' => 500,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // =======================================================
            // 13. MATERIAL KALENDER DINDING (Gantungan Saja)
            // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0059',
            //     'nama_bahan_baku' => 'Klepseng Kalender Dinding (31cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 15.00,
            //     'harga_beli' => 1000,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0060',
            //     'nama_bahan_baku' => 'Spiral Kawat + Hanger Kalender (31cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 25.00,
            //     'harga_beli' => 2500,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 14. MATERIAL KALENDER 38x53 (Roll Meteran & Gantungan)
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0061',
            //     'nama_bahan_baku' => 'Art Paper 120 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 120.00,
            //     'harga_beli' => 7000, // Estimasi harga HPP
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0062',
            //     'nama_bahan_baku' => 'Art Carton 210 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 210.00,
            //     'harga_beli' => 10000, // Estimasi harga HPP
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0063',
            //     'nama_bahan_baku' => 'Art Carton 260 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 260.00,
            //     'harga_beli' => 11000, // Estimasi harga HPP
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0064',
            //     'nama_bahan_baku' => 'Art Carton 310 Gsm (Roll)',
            //     'satuan' => 'Meter Persegi',
            //     'berat_gram_persatuan' => 310.00,
            //     'harga_beli' => 13000, // Estimasi harga HPP (buat jaga-jaga)
            //     'stok_awal' => 200,
            //     'stok_sekarang' => 200,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0065',
            //     'nama_bahan_baku' => 'Klepseng Kalender Dinding (38cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 20.00,
            //     'harga_beli' => 1200,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0066',
            //     'nama_bahan_baku' => 'Spiral Kawat + Hanger Kalender (38cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 35.00,
            //     'harga_beli' => 3000,
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // // =======================================================
            // // 15. MATERIAL KALENDER 46x64 (Gantungan Saja)
            // // =======================================================

            // [
            //     'id_bahan_baku' => 'BAHAN-0067',
            //     'nama_bahan_baku' => 'Klepseng Kalender Dinding (46cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 25.00,
            //     'harga_beli' => 1500, // Estimasi HPP
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // [
            //     'id_bahan_baku' => 'BAHAN-0068',
            //     'nama_bahan_baku' => 'Spiral Kawat + Hanger Kalender (46cm)',
            //     'satuan' => 'Pcs',
            //     'berat_gram_persatuan' => 45.00,
            //     'harga_beli' => 3500, // Estimasi HPP
            //     'stok_awal' => 1000,
            //     'stok_sekarang' => 1000,
            //     'is_active' => true,
            //     'created_at' => $now, 'updated_at' => $now
            // ],
            // Kebutuhan Kain
            [
                'id_bahan_baku' => 'BAHAN-0069',
                'nama_bahan_baku' => 'Kain Satin Flag/Bendera (Roll)',
                'satuan' => 'Meter',
                'berat_gram_persatuan' => 150.00, // Berat per meter
                'harga_beli' => 15000, // HPP per meter
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0070',
                'nama_bahan_baku' => 'Kain TC (Tetoron Cotton) Bendera (Roll)',
                'satuan' => 'Meter',
                'berat_gram_persatuan' => 120.00,
                'harga_beli' => 12000, // HPP per meter
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            // Kebutuhan Finishing (Obras & Tali)
            [
                'id_bahan_baku' => 'BAHAN-0071',
                'nama_bahan_baku' => 'Benang Obras Jahit (Cones)',
                'satuan' => 'Pcs',
                'berat_gram_persatuan' => 250.00,
                'harga_beli' => 18000, // HPP per cones
                'stok_awal' => 50,
                'stok_sekarang' => 50,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0072',
                'nama_bahan_baku' => 'Tali Bendera / Tali Kur',
                'satuan' => 'Meter',
                'berat_gram_persatuan' => 10.00,
                'harga_beli' => 500, // HPP tali per meter
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now
            ],
        ]);
    }
}
