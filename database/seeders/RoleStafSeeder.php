<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleStafSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_staf')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('role_staf')->insert([
            [
                'id_role_staf' => 'ROLE-STAF-ADMIN',
                'role' => 'Admin',
                'created_at' => '2026-05-15 06:15:38',
                'updated_at' => '2026-05-15 06:15:38',
            ],
            [
                'id_role_staf' => 'ROLE-STAF-KASIR',
                'role' => 'Kasir',
                'created_at' => '2026-05-16 06:16:38',
                'updated_at' => '2026-05-16 06:16:38',
            ],
        ]);
    }
}
