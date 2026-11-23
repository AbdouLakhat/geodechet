<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Prépare d’abord les unités, puis les comptes nécessaires à la démonstration.
        $this->call([
            UniteSeeder::class,
            AdminSeeder::class,
            ChauffeurSeeder::class,
        ]);
    }
}
