<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProgrammesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
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
        }

        DB::table('programmes')
            ->whereIn('nom', [
                'Master 1 Informatique',
                'Master 2 Informatique',
                'Master 1 Énergie',
                'Master 2 Énergie',
            ])
            ->update([
                'actif' => false,
                'updated_at' => $now,
            ]);

        $niveauxLicence = [
                [
                    'code' => 'licence_1',
                    'documents' => ['cni_passeport', 'diplome', 'releve_notes', 'releve_notes_terminale', 'lettre_motivation'],
                ],
                [
                    'code' => 'licence_2',
                    'documents' => ['cni_passeport', 'diplome', 'releve_notes_licence_1', 'lettre_motivation'],
                ],
                [
                    'code' => 'licence_3',
                    'documents' => ['cni_passeport', 'diplome', 'releve_notes_licence_1', 'releve_notes_licence_2', 'lettre_motivation'],
                ],
            ];

            $niveauxMaster = [
                [
                    'code' => 'master_1',
                    'documents' => ['cni_passeport', 'diplome', 'releve_notes_licence_1', 'releve_notes_licence_2', 'releve_notes_licence_3', 'cv', 'lettre_motivation', 'lettre_recommandation'],
                ],
                [
                    'code' => 'master_2',
                    'documents' => ['cni_passeport', 'diplome', 'releve_notes_licence_1', 'releve_notes_licence_2', 'releve_notes_licence_3', 'releve_notes_master', 'cv', 'lettre_motivation', 'lettre_recommandation'],
                ],
            ];

            $programmes = [
                [
                    'nom' => 'Classes preparatoires aux grandes ecoles',
                    'niveau' => 'classe_preparatoire',
                    'capacite_accueil' => 120,
                    'description' => 'Cycle préparatoire en 2 ans à Dakar, puis cycle ingénieur en France.',
                    'niveaux' => [
                        [
                            'code' => 'classe_preparatoire',
                            'documents' => ['cni_passeport', 'diplome', 'releve_notes', 'releve_notes_terminale', 'lettre_motivation'],
                        ],
                    ],
                ],
                [
                    'nom' => 'Licence Concepteur de systemes d information',
                    'niveau' => 'licence',
                    'capacite_accueil' => 80,
                    'description' => 'Formation orientée développement web et mobile, administration système et sécurité.',
                    'niveaux' => $niveauxLicence,
                ],
                [
                    'nom' => 'Licence Management de la transition numerique',
                    'niveau' => 'licence',
                    'capacite_accueil' => 80,
                    'description' => 'Formation orientée transformation digitale, données et gestion de projets.',
                    'niveaux' => $niveauxLicence,
                ],
                [
                    'nom' => 'Licence Energie et environnement',
                    'niveau' => 'licence',
                    'capacite_accueil' => 80,
                    'description' => 'Formation orientée énergie, environnement et management énergétique.',
                    'niveaux' => $niveauxLicence,
                ],
                [
                    'nom' => 'Cycle d’ingénieur',
                    'niveau' => 'cycle_ingenieur',
                    'capacite_accueil' => 50,
                    'description' => 'Formation d’ingénieur accessible après un niveau Bac+2.',
                    'niveaux' => [
                        [
                            'code' => 'cycle_ingenieur',
                            'documents' => ['cni_passeport', 'diplome', 'releve_notes_licence_1', 'releve_notes_licence_2', 'lettre_motivation'],
                        ],
                    ],
                ],
                [
                    'nom' => 'Master Informatique',
                    'niveau' => 'master',
                    'capacite_accueil' => 50,
                    'description' => 'Master en génie logiciel, cloud, data et sécurité.',
                    'niveaux' => $niveauxMaster,
                ],
                [
                    'nom' => 'Master Energie',
                    'niveau' => 'master',
                    'capacite_accueil' => 50,
                    'description' => 'Master en énergie et transition environnementale.',
                    'niveaux' => $niveauxMaster,
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
                $codesNiveaux = collect($programme['niveaux'])->pluck('code')->all();
                $niveauIds = DB::table('niveaux')
                    ->whereIn('code', $codesNiveaux)
                    ->pluck('id', 'code');
                $missingNiveauCodes = array_diff($codesNiveaux, $niveauIds->keys()->all());

                if ($missingNiveauCodes !== []) {
                    throw new RuntimeException(
                        'Niveaux introuvables : '.implode(', ', $missingNiveauCodes),
                    );
                }

                DB::table('programme_niveaux')
                    ->where('programme_id', $programmeId)
                    ->whereNotIn('niveau_id', $niveauIds->values()->all())
                    ->update([
                        'actif' => false,
                        'updated_at' => $now,
                    ]);

                foreach ($programme['niveaux'] as $ordreNiveau => $niveau) {
                    $niveauId = $niveauIds[$niveau['code']];

                    DB::table('programme_niveaux')->updateOrInsert(
                        [
                            'programme_id' => $programmeId,
                            'niveau_id' => $niveauId,
                        ],
                        [
                            'ordre' => $ordreNiveau + 1,
                            'actif' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );

                    $programmeNiveauId = DB::table('programme_niveaux')
                        ->where('programme_id', $programmeId)
                        ->where('niveau_id', $niveauId)
                        ->value('id');
                    $typeDocumentIds = DB::table('types_documents')
                        ->whereIn('code', $niveau['documents'])
                        ->pluck('id', 'code');
                    $missingDocumentCodes = array_diff($niveau['documents'], $typeDocumentIds->keys()->all());

                    if ($missingDocumentCodes !== []) {
                        throw new RuntimeException(
                            'Types de documents introuvables : '.implode(', ', $missingDocumentCodes),
                        );
                    }

                    $associations = [];

                    foreach ($niveau['documents'] as $ordreDocument => $codeDocument) {
                        $associations[] = [
                            'programme_niveau_id' => $programmeNiveauId,
                            'type_document_id' => $typeDocumentIds[$codeDocument],
                            'obligatoire' => true,
                            'ordre' => $ordreDocument + 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    DB::table('programme_niveau_type_document')
                        ->where('programme_niveau_id', $programmeNiveauId)
                        ->whereNotIn('type_document_id', $typeDocumentIds->values()->all())
                        ->delete();

                    foreach ($associations as $association) {
                        DB::table('programme_niveau_type_document')->updateOrInsert(
                            [
                                'programme_niveau_id' => $association['programme_niveau_id'],
                                'type_document_id' => $association['type_document_id'],
                            ],
                            [
                                'obligatoire' => $association['obligatoire'],
                                'ordre' => $association['ordre'],
                                'created_at' => $association['created_at'],
                                'updated_at' => $association['updated_at'],
                            ],
                        );
                    }
                }
            }
        });
    }
}
