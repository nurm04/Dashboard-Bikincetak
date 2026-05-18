<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HakAksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('hak_akses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('hak_akses')->insert([
            ['id' => 1, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 1, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:43:00', 'updated_at' => '2026-05-16 03:43:00'],
            ['id' => 2, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 2, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:43:00', 'updated_at' => '2026-05-16 03:43:00'],
            ['id' => 3, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 3, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:43:18', 'updated_at' => '2026-05-16 03:43:18'],
            ['id' => 4, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 4, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:43:36', 'updated_at' => '2026-05-16 03:43:36'],
            ['id' => 5, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 5, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:44:06', 'updated_at' => '2026-05-16 03:44:06'],
            ['id' => 6, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 6, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:44:32', 'updated_at' => '2026-05-16 03:44:32'],
            ['id' => 7, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 7, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:44:50', 'updated_at' => '2026-05-16 03:44:50'],
            ['id' => 8, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 8, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:45:08', 'updated_at' => '2026-05-16 03:45:08'],
            ['id' => 9, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 9, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:45:26', 'updated_at' => '2026-05-16 03:45:26'],
            ['id' => 10, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 10, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:47:23', 'updated_at' => '2026-05-16 03:47:23'],
            ['id' => 11, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 11, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 03:47:50', 'updated_at' => '2026-05-16 03:47:50'],
            ['id' => 12, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 12, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-16 06:07:07', 'updated_at' => '2026-05-16 06:07:07'],
        ]);
    }
}
