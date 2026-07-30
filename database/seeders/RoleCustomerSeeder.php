<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_customer')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('role_customer')->insert([
            [
                'id_role_customer' => 'ROLE-CUST-USER',
                'role' => 'User',
                'created_at' => '2026-05-15 06:15:38',
                'updated_at' => '2026-05-15 06:15:38',
            ],
            [
                'id_role_customer' => 'ROLE-CUST-MEMBER',
                'role' => 'Member',
                'created_at' => '2026-05-16 06:16:38',
                'updated_at' => '2026-05-16 06:16:38',
            ],
            [
                'id_role_customer' => 'ROLE-CUST-RESELLER',
                'role' => 'Reseller',
                'created_at' => '2026-05-16 06:17:38',
                'updated_at' => '2026-05-16 06:17:38',
            ],
        ]);
    }
}
