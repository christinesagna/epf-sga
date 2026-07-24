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
                'libelle' => 'Relevé de notes Bac',
                'description' => 'Relevé des résultats du Baccalauréat',
            ],
            [
                'code' => 'releve_notes_terminale',
                'libelle' => 'Relevé de notes Terminale',
                'description' => 'Relevé de notes de la classe de Terminale',
            ],
            [
                'code' => 'releve_notes_licence_1',
                'libelle' => 'Relevé de notes Licence 1',
                'description' => 'Relevé de notes de la première année de Licence',
            ],
            [
                'code' => 'releve_notes_licence_2',
                'libelle' => 'Relevé de notes Licence 2',
                'description' => 'Relevé de notes de la deuxième année de Licence',
            ],
            [
                'code' => 'releve_notes_licence_3',
                'libelle' => 'Relevé de notes Licence 3',
                'description' => 'Relevé de notes de la troisième année de Licence',
            ],
            [
                'code' => 'releve_notes_master',
                'libelle' => 'Relevé de notes Master',
                'description' => 'Relevé de notes de la première année de Master',
            ],
            [
                'code' => 'diplome',
                'libelle' => 'Diplôme Bac',
                'description' => 'Diplôme ou attestation de réussite au Baccalauréat',
            ],
            [
                'code' => 'lettre_motivation',
                'libelle' => 'Lettre de motivation',
                'description' => 'Peut être saisie dans le formulaire et/ou déposée en fichier',
            ],
            [
                'code' => 'lettre_recommandation',
                'libelle' => 'Lettre de recommandation',
                'description' => 'Demandée pour les programmes de Master',
            ],
        ];

        foreach ($documents as $document) {
            DB::table('types_documents')->insertOrIgnore([
                'code' => $document['code'],
                'libelle' => $document['libelle'],
                'description' => $document['description'],
                'extensions_autorisees' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                'taille_max_mb' => 5,
                'actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
