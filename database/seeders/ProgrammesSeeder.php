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
                'nom' => 'Classes Préparatoires aux Grandes Écoles (CPGE)',
                'niveau' => 'classe_preparatoire',
                'capacite_accueil' => 120,
                'description' => 'Formation scientifique destinée aux étudiants souhaitant poursuivre vers le diplôme d\'ingénieur.',
                'documents' => ['cni_passeport', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence en Conception des Systèmes d\'Information (CSI)',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Licence professionnalisante en développement logiciel, bases de données, génie logiciel, gestion de projet et analyse des systèmes d\'information.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence en Administration Systèmes, Réseaux et Cybersécurité',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Licence professionnalisante axée sur l’administration Linux/Windows, les réseaux, la cybersécurité et le cloud.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence en Management de la Transition Numérique',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Licence professionnalisante dédiée à la transformation digitale, au management de projet, à l’innovation et au marketing numérique.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Licence en Énergie et Environnement',
                'niveau' => 'licence',
                'capacite_accueil' => 80,
                'description' => 'Licence professionnalisante orientée vers les énergies renouvelables, le développement durable et le management énergétique.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Cycle Ingénieur',
                'niveau' => 'cycle_ingenieur',
                'capacite_accueil' => 100,
                'description' => 'Cycle ingénieur de trois ans après une licence ou une classe préparatoire, avec des spécialités en numérique, data, travaux publics et énergie.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation'],
            ],
            [
                'nom' => 'Master en Data Engineering',
                'niveau' => 'master',
                'capacite_accueil' => 50,
                'description' => 'Master spécialisé en data engineering, projets industriels, intelligence artificielle et management de données.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation', 'lettre_recommandation'],
            ],
            [
                'nom' => 'Master en Travaux Publics et Éco-cités',
                'niveau' => 'master',
                'capacite_accueil' => 50,
                'description' => 'Master dédié aux travaux publics, aux éco-cités et à la conception de solutions durables pour les villes de demain.',
                'documents' => ['cni_passeport', 'cv', 'releve_notes', 'diplome', 'lettre_motivation', 'lettre_recommandation'],
            ],
            [
                'nom' => 'Master en Énergie et Environnement',
                'niveau' => 'master',
                'capacite_accueil' => 50,
                'description' => 'Master dédié à l\'énergie, aux énergies renouvelables et à la transition environnementale.',
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
