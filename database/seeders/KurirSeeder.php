<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KurirSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Kurir RajaOngkir
        $kurirRajaOngkir = [
            'jne'      => 'JNE Express',
            'pos'      => 'POS Indonesia',
            'tiki'     => 'TIKI',
            'sicepat'  => 'SiCepat Ekspres',
            'jnt'      => 'J&T Express',
            'ninja'    => 'Ninja Xpress',
            'anteraja' => 'AnterAja',
            'lion'     => 'Lion Parcel',
            'wahana'   => 'Wahana Express',
            'ide'      => 'ID Express',
            'sentral'  => 'Sentral Cargo'
        ];

        // 2. Data Kurir Lainnya (Manual / Lokal)
        $kurirLainnya = [
            'gojek'      => 'GoSend',
            'grab'       => 'GrabExpress',
            'lalamove'   => 'Lalamove',
            'kurir_toko' => 'Kurir Toko (Internal)'
        ];

        $dataToInsert = [];

        // Mapping Kurir RajaOngkir
        foreach ($kurirRajaOngkir as $kunci => $nama) {
            $dataToInsert[] = [
                'grup'       => 'kurir_rajaongkir',
                'kunci'      => $kunci,
                'nilai'      => json_encode([
                    'nama'  => $nama,
                    'image' => null // Bisa diisi path default jika ada: 'img_web/couriers/'.$kunci.'.png'
                ]),
                'tipe_data'  => 'json',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Mapping Kurir Lainnya
        foreach ($kurirLainnya as $kunci => $nama) {
            $dataToInsert[] = [
                'grup'       => 'kurir_lainnya',
                'kunci'      => $kunci,
                'nilai'      => json_encode([
                    'nama'  => $nama,
                    'image' => null
                ]),
                'tipe_data'  => 'json',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Simpan ke database (Gunakan upsert agar tidak error duplicate entry pada field 'kunci' jika di-seed ulang)
        DB::table('pengaturan_web')->upsert(
            $dataToInsert,
            ['kunci'], // Kolom unique untuk mengecek data yang sama
            ['grup', 'nilai', 'tipe_data', 'updated_at'] // Kolom yang diupdate jika kunci sudah ada
        );
    }
}
