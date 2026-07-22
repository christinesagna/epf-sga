<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NiveauxSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $niveaux = [
            'classe_preparatoire' => 'Classes préparatoires',
            'licence_1' => 'Licence 1',
            'licence_2' => 'Licence 2',
            'licence_3' => 'Licence 3',
            'cycle_ingenieur' => 'Cycle d’ingénieur',
            'master_1' => 'Master 1',
            'master_2' => 'Master 2',
        ];

        foreach ($niveaux as $code => $libelle) {
            DB::table('niveaux')->updateOrInsert(
                ['code' => $code],
                [
                    'libelle' => $libelle,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}
