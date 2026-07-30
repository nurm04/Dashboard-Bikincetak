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
            ['id' => 1, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 1, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-15 20:43:00', 'updated_at' => '2026-05-15 20:43:00'],
            ['id' => 5, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 5, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-15 20:44:06', 'updated_at' => '2026-05-15 20:44:06'],
            ['id' => 6, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 6, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-15 20:44:32', 'updated_at' => '2026-05-15 20:44:32'],
            ['id' => 7, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 7, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-15 20:44:50', 'updated_at' => '2026-05-15 20:44:50'],
            ['id' => 8, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 8, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-15 20:45:08', 'updated_at' => '2026-05-15 20:45:08'],
            ['id' => 9, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 9, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-05-15 20:45:26', 'updated_at' => '2026-05-15 20:45:26'],
            ['id' => 13, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 13, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-06-12 23:12:57', 'updated_at' => '2026-06-12 23:12:57'],
            ['id' => 17, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 17, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-20 14:11:14', 'updated_at' => '2026-07-20 14:11:14'],
            ['id' => 18, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 17, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-20 14:11:14', 'updated_at' => '2026-07-20 14:11:14'],
            ['id' => 19, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 3, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:12:23', 'updated_at' => '2026-07-21 03:12:23'],
            ['id' => 20, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 3, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:12:23', 'updated_at' => '2026-07-21 03:12:23'],
            ['id' => 21, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 3, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:12:23', 'updated_at' => '2026-07-21 03:12:23'],
            ['id' => 22, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 4, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:12:37', 'updated_at' => '2026-07-21 03:12:37'],
            ['id' => 23, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 4, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:12:37', 'updated_at' => '2026-07-21 03:12:37'],
            ['id' => 24, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 4, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:12:37', 'updated_at' => '2026-07-21 03:12:37'],
            ['id' => 25, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 10, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:13:03', 'updated_at' => '2026-07-21 03:13:03'],
            ['id' => 26, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 10, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:13:03', 'updated_at' => '2026-07-21 03:13:03'],
            ['id' => 27, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 10, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:13:03', 'updated_at' => '2026-07-21 03:13:03'],
            ['id' => 28, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 11, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:13:22', 'updated_at' => '2026-07-21 03:13:22'],
            ['id' => 29, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 11, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:13:22', 'updated_at' => '2026-07-21 03:13:22'],
            ['id' => 30, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 11, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:13:22', 'updated_at' => '2026-07-21 03:13:22'],
            ['id' => 31, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 12, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:13:45', 'updated_at' => '2026-07-21 03:13:45'],
            ['id' => 32, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 12, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:13:45', 'updated_at' => '2026-07-21 03:13:45'],
            ['id' => 33, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 12, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:13:45', 'updated_at' => '2026-07-21 03:13:45'],
            ['id' => 34, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 14, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:14:09', 'updated_at' => '2026-07-21 03:14:09'],
            ['id' => 35, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 14, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:14:09', 'updated_at' => '2026-07-21 03:14:09'],
            ['id' => 36, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 15, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:14:34', 'updated_at' => '2026-07-21 03:14:34'],
            ['id' => 37, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 15, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:14:34', 'updated_at' => '2026-07-21 03:14:34'],
            ['id' => 38, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 16, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-21 03:14:51', 'updated_at' => '2026-07-21 03:14:51'],
            ['id' => 39, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 16, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:14:51', 'updated_at' => '2026-07-21 03:14:51'],
            ['id' => 40, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 16, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 0, 'created_at' => '2026-07-21 03:14:51', 'updated_at' => '2026-07-21 03:14:51'],
            ['id' => 43, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 18, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-22 04:16:41', 'updated_at' => '2026-07-22 04:16:41'],
            ['id' => 44, 'id_role_staf' => 'ROLE-STAF-KASIR', 'modul_id' => 18, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 0, 'created_at' => '2026-07-22 04:16:41', 'updated_at' => '2026-07-22 04:16:41'],
            ['id' => 45, 'id_role_staf' => 'ROLE-STAF-PRODUKSI', 'modul_id' => 18, 'bisa_buka' => 1, 'bisa_tambah' => 0, 'bisa_ubah' => 0, 'bisa_hapus' => 0, 'created_at' => '2026-07-22 04:16:41', 'updated_at' => '2026-07-22 04:16:41'],
            ['id' => 46, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 19, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-25 03:25:54', 'updated_at' => '2026-07-25 03:25:54'],
            ['id' => 47, 'id_role_staf' => 'ROLE-STAF-ADMIN', 'modul_id' => 2, 'bisa_buka' => 1, 'bisa_tambah' => 1, 'bisa_ubah' => 1, 'bisa_hapus' => 1, 'created_at' => '2026-07-25 23:07:49', 'updated_at' => '2026-07-25 23:07:49'],
        ]);
    }
}
