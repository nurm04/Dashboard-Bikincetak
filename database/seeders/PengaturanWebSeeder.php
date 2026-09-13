<?php

namespace Database\Seeders;

use App\Models\PengaturanWeb;
use Illuminate\Database\Seeder;

class PengaturanWebSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ==========================================
            // 1. GRUP: IDENTITAS & SEO (Sesuai Data Metadata Next.js)
            // ==========================================
            [
                'grup' => 'identitas',
                'kunci' => 'nama_website',
                'nilai' => 'BikinCetak - Platform Digital Printing Modern & Cepat',
                'tipe_data' => 'text'
            ],
            [
                'grup' => 'identitas',
                'kunci' => 'deskripsi_singkat',
                'nilai' => 'Pesan kebutuhan cetak Anda secara online dengan mudah, cepat, dan berkualitas tinggi di BikinCetak. Melayani cetak sticker, banner, dan merchandise.',
                'tipe_data' => 'longtext'
            ],
            [
                'grup' => 'identitas',
                'kunci' => 'keyword_seo',
                'nilai' => 'percetakan online, digital printing, cetak stiker, cetak banner, bikin cetak, cetak kalender',
                'tipe_data' => 'text'
            ],
            [
                'grup' => 'identitas',
                'kunci' => 'logo_utama',
                'nilai' => 'img_web/logobikincetak.png',
                'tipe_data' => 'image'
            ],

            // ==========================================
            // 2. GRUP: KONTAK & SOSMED (JSON Array)
            // ==========================================
            [
                'grup' => 'kontak',
                'kunci' => 'daftar_whatsapp',
                'tipe_data' => 'json',
                'nilai' => json_encode([
                    [
                        'id' => uniqid(),
                        'nama' => 'CS Layanan Cetak',
                        'nomor' => '6281213139490',
                        'pesan_default' => 'Halo Admin BikinCetak, saya ingin bertanya tentang cetak...',
                        'is_active' => true
                    ],
                    [
                        'id' => uniqid(),
                        'nama' => 'CS Komplain & Pengiriman',
                        'nomor' => '6283831862770',
                        'pesan_default' => 'Halo, saya ingin menanyakan status pesanan saya.',
                        'is_active' => true
                    ]
                ])
            ],
            [
                'grup' => 'kontak',
                'kunci' => 'daftar_sosmed',
                'tipe_data' => 'json',
                'nilai' => json_encode([
                    ['platform' => 'Instagram', 'url' => 'https://instagram.com/bikincetak', 'icon' => 'instagram', 'is_active' => true],
                    ['platform' => 'TikTok', 'url' => 'https://tiktok.com/@bikincetak', 'icon' => 'tiktok', 'is_active' => true],
                    ['platform' => 'Facebook', 'url' => 'https://facebook.com/bikincetak.id', 'icon' => 'facebook', 'is_active' => true]
                ])
            ],
            [
                'grup' => 'kontak',
                'kunci' => 'informasi_lokasi',
                'tipe_data' => 'json',
                'nilai' => json_encode([
                    'email' => 'info@bikincetak.co.id',
                    'alamat_lengkap' => 'Layanan Online - Seluruh Indonesia',
                    'link_gmaps' => 'https://maps.app.goo.gl/VwC6C6tzCZ8CwPSK8'
                ])
            ],

            // ==========================================
            // 3. GRUP: PEMBAYARAN (Dilengkapi Parameter Gambar Icon)
            // ==========================================
            [
                'grup' => 'pembayaran',
                'kunci' => 'metode_pembayaran',
                'tipe_data' => 'json',
                'nilai' => json_encode([
                    [
                        'id' => uniqid(),
                        'nama_metode' => 'Bank BCA',
                        'no_rekening' => '1234567890',
                        'atas_nama' => 'PT Bikin Cetak',
                        'icon_url' => '/bca.png',
                        'is_active' => true
                    ],
                    [
                        'id' => uniqid(),
                        'nama_metode' => 'QRIS',
                        'no_rekening' => '-',
                        'atas_nama' => 'Bikin Cetak Official',
                        'icon_url' => '/qris.png',
                        'is_active' => true
                    ]
                ])
            ],
            [
                'grup' => 'pembayaran',
                'kunci' => 'teks_info_pembayaran',
                'nilai' => 'Menerima pembayaran melalui transfer Bank BCA dan seluruh E-Wallet / M-Banking via QRIS.',
                'tipe_data' => 'text'
            ],
        ];

        foreach ($settings as $setting) {
            PengaturanWeb::updateOrCreate(
                ['kunci' => $setting['kunci']],
                $setting
            );
        }
    }
}
