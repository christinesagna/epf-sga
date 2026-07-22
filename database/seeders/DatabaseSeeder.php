<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TypesDocumentsSeeder::class,
            NiveauxSeeder::class,
            ProgrammesSeeder::class,
            SuperAdministrateurSeeder::class,
        ]);
    }
}
