<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AkunSeeder::class,
            RoleStafSeeder::class,
            ModulSeeder::class,
            StafSeeder::class,
            HakAksesSeeder::class,
        ]);
    }
}
