<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StafSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('staf')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('staf')->insert([
            [
                'id_staf' => 'STAF-20260516-001',
                'user_id' => 1,
                'no_hp' => '085785061834',
                'id_role_staf' => 'ROLE-STAF-ADMIN',
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id_staf' => 'STAF-20260516-002',
                'user_id' => 2,
                'no_hp' => '081231231231',
                'id_role_staf' => 'ROLE-STAF-KASIR',
                'created_at' => '2026-05-16 06:21:47',
                'updated_at' => '2026-05-16 06:21:47',
            ],
        ]);
    }
}
