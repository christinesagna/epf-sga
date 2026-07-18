<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgrammesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $programmes = [
            [
                'nom' => 'Classes preparatoires aux grandes ecoles',
                'niveau' => 'classe_preparatoire',
                'capacite_accueil' => 120,
                'description' => 'Cycle preparatoire en 2 ans a Dakar, puis cycle ingenieur en France.',
                'documents' => ['cni_passeport', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence Concepteur de systemes d information',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Formation orientee developpement web et mobile, administration systeme et securite.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence Management de la transition numerique',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Formation orientee transformation digitale, donnees et gestion de projets.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence Energie et environnement',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Formation orientee energie, environnement et management energetique.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Master Informatique',
                'niveau' => 'master',
                'capacite_accueil' => 50,
                'description' => 'Approfondissement en genie logiciel, cloud, data et securite.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation', 'lettre_recommandation'],
            ],
            [
                'nom' => 'Master Energie',
                'niveau' => 'master',
                'capacite_accueil' => 50,
                'description' => 'Approfondissement sur les energies, l efficacite energetique et la transition environnementale.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation', 'lettre_recommandation'],
            ],
        ];

        foreach ($programmes as $programme) {
            DB::table('programmes')->updateOrInsert(
                ['nom' => $programme['nom']],
                [
                    'slug' => Str::slug($programme['nom']),
                    'niveau' => $programme['niveau'],
                    'capacite_accueil' => $programme['capacite_accueil'],
                    'date_ouverture' => '2026-01-01',
                    'date_fermeture' => '2026-10-31',
                    'frais_scolarite' => null,
                    'echeancier_paiement' => null,
                    'description' => $programme['description'],
                    'actif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $programmeId = DB::table('programmes')
                ->where('nom', $programme['nom'])
                ->value('id');

            foreach ($programme['documents'] as $ordre => $codeDocument) {
                $typeDocumentId = DB::table('types_documents')
                    ->where('code', $codeDocument)
                    ->value('id');

                if (! $typeDocumentId) {
                    continue;
                }

                DB::table('programme_type_document')->updateOrInsert(
                    [
                        'programme_id' => $programmeId,
                        'type_document_id' => $typeDocumentId,
                    ],
                    [
                        'obligatoire' => true,
                        'ordre' => $ordre + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
