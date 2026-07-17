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
            // --- KATEGORI KERTAS & DIGITAL PRINT ---
            [
                'id_bahan_baku' => 'BAHAN-0001',
                'nama_bahan_baku' => 'Albatros',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 136.5,
                'harga_beli' => 2500,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0002',
                'nama_bahan_baku' => 'Laminasi Doff',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 136.5,
                'harga_beli' => 2500,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0003',
                'nama_bahan_baku' => 'Laminasi Glossy',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 136.5,
                'harga_beli' => 2500,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0004',
                'nama_bahan_baku' => 'Photopaper',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 136.5,
                'harga_beli' => 2500,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0005',
                'nama_bahan_baku' => 'Kertas Art Carton 260gr Plano (61x86)',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 136.5,
                'harga_beli' => 2500,
                'stok_awal' => 1000,
                'stok_sekarang' => 1000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0006',
                'nama_bahan_baku' => 'Kertas Art Paper 150gr Plano (61x86)',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 78.7,
                'harga_beli' => 1500,
                'stok_awal' => 2000,
                'stok_sekarang' => 2000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0007',
                'nama_bahan_baku' => 'Kertas HVS 80gr Putih Plano (61x86)',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 42.0,
                'harga_beli' => 800,
                'stok_awal' => 5000,
                'stok_sekarang' => 5000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- KATEGORI STICKER ---
            [
                'id_bahan_baku' => 'BAHAN-0008',
                'nama_bahan_baku' => 'Sticker Vinyl Susu Camel Digiprint A3+',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 35.0,
                'harga_beli' => 4500,
                'stok_awal' => 500,
                'stok_sekarang' => 500,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0009',
                'nama_bahan_baku' => 'Sticker Chromo Lintec Plano',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 110.0,
                'harga_beli' => 1800,
                'stok_awal' => 1500,
                'stok_sekarang' => 1500,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- KATEGORI BANNER / MEDIA PROMOSI (Bahan Rol) ---
            [
                'id_bahan_baku' => 'BAHAN-0010',
                'nama_bahan_baku' => 'Bahan Banner Flexi China 280gr (3.2m x 50m)',
                'satuan' => 'Rol',
                'berat_gram_persatuan' => 44800.0, // 44.8 Kg per rol
                'harga_beli' => 1250000,
                'stok_awal' => 10,
                'stok_sekarang' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0011',
                'nama_bahan_baku' => 'Bahan Banner Albatros Indoor (1.25m x 30m)',
                'satuan' => 'Rol',
                'berat_gram_persatuan' => 8500.0,
                'harga_beli' => 750000,
                'stok_awal' => 15,
                'stok_sekarang' => 15,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- KATEGORI DISPLAY / STAND BANNER ---
            [
                'id_bahan_baku' => 'BAHAN-0012',
                'nama_bahan_baku' => 'Kerangka X-Banner Black Fiber (60x160)',
                'satuan' => 'Pcs',
                'berat_gram_persatuan' => 300.0,
                'harga_beli' => 15000,
                'stok_awal' => 100,
                'stok_sekarang' => 100,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0013',
                'nama_bahan_baku' => 'Kerangka Roll Up Banner Aluminium (85x200)',
                'satuan' => 'Pcs',
                'berat_gram_persatuan' => 2100.0,
                'harga_beli' => 110000,
                'stok_awal' => 50,
                'stok_sekarang' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- KATEGORI BUKU NOTA (NCR) ---
            [
                'id_bahan_baku' => 'BAHAN-0014',
                'nama_bahan_baku' => 'Kertas NCR Top White Plano',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 29.0,
                'harga_beli' => 650,
                'stok_awal' => 3000,
                'stok_sekarang' => 3000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0015',
                'nama_bahan_baku' => 'Kertas NCR Middle Pink/Yellow/Blue Plano',
                'satuan' => 'Lembar',
                'berat_gram_persatuan' => 26.0,
                'harga_beli' => 600,
                'stok_awal' => 6000,
                'stok_sekarang' => 6000,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- KATEGORI SOUVENIR, APPAREL & KEMASAN ---
            [
                'id_bahan_baku' => 'BAHAN-0016',
                'nama_bahan_baku' => 'Mug Keramik Polos Coating Import White',
                'satuan' => 'Pcs',
                'berat_gram_persatuan' => 350.0,
                'harga_beli' => 7500,
                'stok_awal' => 360,
                'stok_sekarang' => 360,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0017',
                'nama_bahan_baku' => 'Kain Kanvas Serat Katun Greige (Lebar 1.5m)',
                'satuan' => 'Meter',
                'berat_gram_persatuan' => 400.0,
                'harga_beli' => 22000,
                'stok_awal' => 100,
                'stok_sekarang' => 100,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id_bahan_baku' => 'BAHAN-0018',
                'nama_bahan_baku' => 'Dudukan Tatakan Kalender Meja Hardboard Linen',
                'satuan' => 'Pcs',
                'berat_gram_persatuan' => 150.0,
                'harga_beli' => 4500,
                'stok_awal' => 400,
                'stok_sekarang' => 400,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
