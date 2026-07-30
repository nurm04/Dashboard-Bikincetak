<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinishingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('finishing')->insert([
            ['id_finishing' => 'FIN-001', 'nama_finishing' => 'Laminasi', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-002', 'nama_finishing' => 'Sisi Cetak', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-003', 'nama_finishing' => 'Potong Pola', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-004', 'nama_finishing' => 'Print UV', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-005', 'nama_finishing' => 'Kaki Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-006', 'nama_finishing' => 'Cover Buku', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-007', 'nama_finishing' => 'Laminasi Cover Buku', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-008', 'nama_finishing' => 'Laminasi Isi Buku', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-009', 'nama_finishing' => 'Jilid Buku', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-010', 'nama_finishing' => 'Isi Halaman Kalender', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-011', 'nama_finishing' => 'Klepsng / Spiral Kawat', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-012', 'nama_finishing' => 'Tambah Kotak', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-013', 'nama_finishing' => 'Tambah Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-014', 'nama_finishing' => 'Cetak Kartu ID Card', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-015', 'nama_finishing' => 'Holder Case ID Card', 'created_at' => $now, 'updated_at' => $now],
            ['id_finishing' => 'FIN-016', 'nama_finishing' => 'Stopper Tali Lanyard', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('pilihan_finishing')->insert([
            ['id_pilihan_finishing' => 'FIN-001-001', 'id_finishing' => 'FIN-001', 'nama_pilihan' => 'Doff', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-001-002', 'id_finishing' => 'FIN-001', 'nama_pilihan' => 'Glossy', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-002-001', 'id_finishing' => 'FIN-002', 'nama_pilihan' => 'Satu Sisi', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-002-002', 'id_finishing' => 'FIN-002', 'nama_pilihan' => 'Dua Sisi', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-003-001', 'id_finishing' => 'FIN-003', 'nama_pilihan' => 'Kiss Cut', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-003-002', 'id_finishing' => 'FIN-003', 'nama_pilihan' => 'Die Cut', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-004-001', 'id_finishing' => 'FIN-004', 'nama_pilihan' => 'Print UV Highres', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-005-001', 'id_finishing' => 'FIN-005', 'nama_pilihan' => 'Kaki x Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-005-002', 'id_finishing' => 'FIN-005', 'nama_pilihan' => 'Kaki Y Banner', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-006-001', 'id_finishing' => 'FIN-006', 'nama_pilihan' => 'Art Carton 210 Gsm A3+', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-006-002', 'id_finishing' => 'FIN-006', 'nama_pilihan' => 'Art Carton 260 Gsm A3+', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-007-001', 'id_finishing' => 'FIN-007', 'nama_pilihan' => 'Doff', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-007-002', 'id_finishing' => 'FIN-007', 'nama_pilihan' => 'Glossy', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-008-001', 'id_finishing' => 'FIN-008', 'nama_pilihan' => 'Doff', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-008-002', 'id_finishing' => 'FIN-008', 'nama_pilihan' => 'Glossy', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-009-001', 'id_finishing' => 'FIN-009', 'nama_pilihan' => 'Jilid Staples', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-009-002', 'id_finishing' => 'FIN-009', 'nama_pilihan' => 'Jilid Softcover', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-009-003', 'id_finishing' => 'FIN-009', 'nama_pilihan' => 'Jilid Hardcover', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-001', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '1 Lembar 12 Bulanan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-002', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '2 Lembar 6 Bulanan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-003', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '3 Lembar 4 Bulanan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-004', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '4 Lembar 3 Bulanan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-005', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '4 Lembar 3 Bulanan + 1 Lembar Cover', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-006', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '6 Lembar 2 Bulanan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-007', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '6 Lembar 2 Bulanan + 1 Lembar Cover', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-008', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '12 Lembar 1 Bulanan', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-010-009', 'id_finishing' => 'FIN-010', 'nama_pilihan' => '12 Lembar 1 Bulanan + 1 Lembar Cover', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-011-001', 'id_finishing' => 'FIN-011', 'nama_pilihan' => 'Klepseng', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-011-002', 'id_finishing' => 'FIN-011', 'nama_pilihan' => 'Spiral Kawat', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-012-001', 'id_finishing' => 'FIN-012', 'nama_pilihan' => 'Kotak Kartu Nama', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-013-001', 'id_finishing' => 'FIN-013', 'nama_pilihan' => 'Tambah 1 Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-013-002', 'id_finishing' => 'FIN-013', 'nama_pilihan' => 'Tambah 2 Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-013-003', 'id_finishing' => 'FIN-013', 'nama_pilihan' => 'Tambah 3 Warna', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-014-001', 'id_finishing' => 'FIN-014', 'nama_pilihan' => 'ID Card Name Tag Print UV', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-015-001', 'id_finishing' => 'FIN-015', 'nama_pilihan' => 'Holder ID Card', 'created_at' => $now, 'updated_at' => $now],
            ['id_pilihan_finishing' => 'FIN-016-001', 'id_finishing' => 'FIN-016', 'nama_pilihan' => 'Stopper Tali Lanyard', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
