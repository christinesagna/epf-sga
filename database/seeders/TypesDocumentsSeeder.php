<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $documents = [
            [
                'code' => 'cni_passeport',
                'libelle' => 'CNI ou Passeport',
                'description' => 'Pièce d’identité valide',
            ],
            [
                'code' => 'cv',
                'libelle' => 'Curriculum Vitae',
                'description' => 'CV du candidat',
            ],
            [
                'code' => 'releve_notes',
                'libelle' => 'Relevés de notes',
                'description' => 'Relevés selon le niveau visé',
            ],
            [
                'code' => 'diplome',
                'libelle' => 'Diplôme',
                'description' => 'Diplôme ou attestation',
            ],
            [
                'code' => 'lettre_motivation',
                'libelle' => 'Lettre de motivation',
                'description' => 'Peut être saisie dans le formulaire et/ou déposée en fichier',
            ],
            [
                'code' => 'lettre_recommandation',
                'libelle' => 'Lettre de recommandation',
                'description' => 'Demandée pour certains programmes',
            ],
        ];

        foreach ($documents as $document) {
            DB::table('types_documents')->updateOrInsert(
                ['code' => $document['code']],
                [
                    'libelle' => $document['libelle'],
                    'description' => $document['description'],
                    'extensions_autorisees' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                    'taille_max_mb' => 5,
                    'actif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
