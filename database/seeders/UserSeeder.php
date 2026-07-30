<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123123123'),
                'role' => 'staf',
                'remember_token' => null,
                'created_at' => '2026-05-16 03:32:05',
                'updated_at' => '2026-05-16 03:32:05',
            ],
            [
                'id' => 2,
                'name' => 'Kasir1',
                'email' => 'kasir1@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123123123'),
                'role' => 'staf',
                'remember_token' => null,
                'created_at' => '2026-05-16 06:21:47',
                'updated_at' => '2026-05-16 06:21:47',
            ],
            [
                'id' => 3,
                'name' => 'Produksi1',
                'email' => 'produksi1@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123123123'),
                'role' => 'staf',
                'remember_token' => null,
                'created_at' => '2026-05-16 06:22:47',
                'updated_at' => '2026-05-16 06:22:47',
            ],
            [
                'id' => 4,
                'name' => 'Desain1',
                'email' => 'desain1@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123123123'),
                'role' => 'staf',
                'remember_token' => null,
                'created_at' => '2026-05-16 06:33:47',
                'updated_at' => '2026-05-16 06:33:47',
            ],
        ]);
    }
}
