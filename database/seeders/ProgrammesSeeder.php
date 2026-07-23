<?php

namespace Database\Seeders;

use Carbon\CarbonInterface;
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
                $this->enregistrerProgramme($programme, $now);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $programme
     */
    private function enregistrerProgramme(array $programme, CarbonInterface $now): void
    {
        DB::table('programmes')->insertOrIgnore([
            'nom' => $programme['nom'],
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
        ]);

        $programmeId = DB::table('programmes')
            ->where('nom', $programme['nom'])
            ->value('id');
        $codesNiveaux = collect($programme['niveaux'])->pluck('code')->all();
        $niveauIds = DB::table('niveaux')
            ->whereIn('code', $codesNiveaux)
            ->pluck('id', 'code');
        $missingNiveauCodes = array_diff($codesNiveaux, $niveauIds->keys()->all());

        if ($missingNiveauCodes !== []) {
            throw new RuntimeException('Niveaux introuvables : '.implode(', ', $missingNiveauCodes));
        }

        foreach ($programme['niveaux'] as $ordreNiveau => $niveau) {
            $this->enregistrerNiveau(
                $programmeId,
                $niveauIds[$niveau['code']],
                $niveau,
                $ordreNiveau + 1,
                $now,
            );
        }
    }

    /**
     * @param  array{code: string, documents: list<string>}  $niveau
     */
    private function enregistrerNiveau(
        int $programmeId,
        int $niveauId,
        array $niveau,
        int $ordre,
        CarbonInterface $now,
    ): void {
        DB::table('programme_niveaux')->insertOrIgnore([
            'programme_id' => $programmeId,
            'niveau_id' => $niveauId,
            'ordre' => $ordre,
            'actif' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $programmeNiveauId = DB::table('programme_niveaux')
            ->where('programme_id', $programmeId)
            ->where('niveau_id', $niveauId)
            ->value('id');
        $typeDocumentIds = DB::table('types_documents')
            ->whereIn('code', $niveau['documents'])
            ->pluck('id', 'code');
        $missingDocumentCodes = array_diff($niveau['documents'], $typeDocumentIds->keys()->all());

        if ($missingDocumentCodes !== []) {
            throw new RuntimeException('Types de documents introuvables : '.implode(', ', $missingDocumentCodes));
        }

        foreach ($niveau['documents'] as $ordreDocument => $codeDocument) {
            DB::table('programme_niveau_type_document')->insertOrIgnore([
                'programme_niveau_id' => $programmeNiveauId,
                'type_document_id' => $typeDocumentIds[$codeDocument],
                'obligatoire' => true,
                'ordre' => $ordreDocument + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
