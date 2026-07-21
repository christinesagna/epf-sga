<?php

namespace Tests\Feature;

use App\Models\Candidature;
use App\Models\Programme;
use Database\Seeders\ProgrammesSeeder;
use Database\Seeders\TypesDocumentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProgrammeDocumentsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_programme_expose_ses_niveaux_et_leurs_documents(): void
    {
        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $this->assertDatabaseCount('types_documents', 11);
        $this->assertDatabaseCount('programmes', 7);
        $this->assertDatabaseCount('programme_niveaux', 15);
        $this->assertDatabaseCount('programme_niveau_type_document', 86);

        $documentsPostBac = [
            'cni_passeport',
            'diplome',
            'releve_notes',
            'releve_notes_terminale',
            'lettre_motivation',
        ];

        $documentsMaster1 = [
            'cni_passeport',
            'diplome',
            'releve_notes_licence_1',
            'releve_notes_licence_2',
            'releve_notes_licence_3',
            'cv',
            'lettre_motivation',
            'lettre_recommandation',
        ];

        $documentsMaster2 = [
            'cni_passeport',
            'diplome',
            'releve_notes_licence_1',
            'releve_notes_licence_2',
            'releve_notes_licence_3',
            'releve_notes_master',
            'cv',
            'lettre_motivation',
            'lettre_recommandation',
        ];

        $attentes = [
            'Classes preparatoires aux grandes ecoles' => [
                'classe_preparatoire' => $documentsPostBac,
            ],
            'Licence Concepteur de systemes d information' => $this->attentesLicence(),
            'Licence Management de la transition numerique' => $this->attentesLicence(),
            'Licence Energie et environnement' => $this->attentesLicence(),
            'Cycle d’ingénieur' => [
                'cycle_ingenieur' => [
                    'cni_passeport',
                    'diplome',
                    'releve_notes_licence_1',
                    'releve_notes_licence_2',
                    'lettre_motivation',
                ],
            ],
            'Master Informatique' => [
                'master_1' => $documentsMaster1,
                'master_2' => $documentsMaster2,
            ],
            'Master Energie' => [
                'master_1' => $documentsMaster1,
                'master_2' => $documentsMaster2,
            ],
        ];

        $programmes = Programme::query()
            ->where('actif', true)
            ->with([
                'niveaux' => fn ($query) => $query
                    ->where('actif', true)
                    ->with('typesDocuments'),
            ])
            ->get()
            ->keyBy('nom');

        $this->assertCount(count($attentes), $programmes);

        foreach ($attentes as $nomProgramme => $niveauxAttendus) {
            $programme = $programmes->get($nomProgramme);

            $this->assertNotNull($programme, "Le programme {$nomProgramme} est introuvable.");
            $this->assertSame(array_keys($niveauxAttendus), $programme->niveaux->pluck('code')->all());

            foreach ($niveauxAttendus as $codeNiveau => $codesDocuments) {
                $niveau = $programme->niveaux->firstWhere('code', $codeNiveau);

                $this->assertNotNull($niveau, "Le niveau {$codeNiveau} est introuvable.");
                $this->assertSame($codesDocuments, $niveau->typesDocuments->pluck('code')->all());
                $this->assertTrue(
                    $niveau->typesDocuments
                        ->every(fn ($document): bool => (bool) $document->pivot->obligatoire),
                );

                if (! str_starts_with($codeNiveau, 'master_')) {
                    $this->assertNotContains('cv', $niveau->typesDocuments->pluck('code')->all());
                }
            }
        }

        $candidatId = DB::table('candidats')->insertGetId([
            'email' => 'candidat-niveau@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $programmeMaster = $programmes->get('Master Informatique');
        $niveauMaster2 = $programmeMaster->niveaux->firstWhere('code', 'master_2');

        $candidature = Candidature::query()->create([
            'candidat_id' => $candidatId,
            'programme_id' => $programmeMaster->id,
            'programme_niveau_id' => $niveauMaster2->id,
            'edit_token' => fake()->uuid(),
        ]);

        $this->assertTrue($candidature->programmeNiveau->is($niveauMaster2));
        $this->assertDatabaseHas('candidatures', [
            'id' => $candidature->id,
            'programme_id' => $programmeMaster->id,
            'programme_niveau_id' => $niveauMaster2->id,
        ]);
    }

    public function test_les_seeders_sont_idempotents_et_desactivent_les_masters_segmentes(): void
    {
        $this->seed(TypesDocumentsSeeder::class);

        $maintenant = now();
        $mastersSegmentes = [];

        foreach (['Master 1 Informatique', 'Master 2 Informatique', 'Master 1 Énergie', 'Master 2 Énergie'] as $nom) {
            $mastersSegmentes[$nom] = DB::table('programmes')->insertGetId([
                'nom' => $nom,
                'slug' => str($nom)->slug(),
                'niveau' => str($nom)->contains('Master 1') ? 'master_1' : 'master_2',
                'capacite_accueil' => 50,
                'actif' => true,
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ]);
        }

        $this->seed(ProgrammesSeeder::class);

        $niveauLicence1 = DB::table('programme_niveaux')
            ->join('programmes', 'programmes.id', '=', 'programme_niveaux.programme_id')
            ->where('programmes.nom', 'Licence Energie et environnement')
            ->where('programme_niveaux.code', 'licence_1')
            ->value('programme_niveaux.id');
        $cvId = DB::table('types_documents')->where('code', 'cv')->value('id');

        DB::table('programme_niveau_type_document')->insert([
            'programme_niveau_id' => $niveauLicence1,
            'type_document_id' => $cvId,
            'obligatoire' => true,
            'ordre' => 99,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);

        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        foreach ($mastersSegmentes as $nom => $id) {
            $this->assertDatabaseHas('programmes', [
                'id' => $id,
                'nom' => $nom,
                'actif' => false,
            ]);
        }

        $this->assertDatabaseCount('types_documents', 11);
        $this->assertDatabaseCount('programmes', 11);
        $this->assertDatabaseCount('programme_niveaux', 15);
        $this->assertDatabaseCount('programme_niveau_type_document', 86);
        $this->assertSame(7, Programme::query()->where('actif', true)->count());
        $this->assertDatabaseMissing('programme_niveau_type_document', [
            'programme_niveau_id' => $niveauLicence1,
            'type_document_id' => $cvId,
        ]);
    }

    public function test_la_vue_presente_les_associations_avec_des_libelles_explicites(): void
    {
        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $documentsMaster2 = DB::table('vue_programme_niveau_documents')
            ->where('programme_nom', 'Master Informatique')
            ->where('niveau_code', 'master_2')
            ->orderBy('ordre')
            ->get();

        $this->assertSame('Master 2', $documentsMaster2->first()->niveau_libelle);
        $this->assertSame(
            [
                'CNI ou Passeport',
                'Diplôme Bac',
                'Relevé de notes Licence 1',
                'Relevé de notes Licence 2',
                'Relevé de notes Licence 3',
                'Relevé de notes Master',
                'Curriculum Vitae',
                'Lettre de motivation',
                'Lettre de recommandation',
            ],
            $documentsMaster2->pluck('document_libelle')->all(),
        );
        $this->assertTrue(
            $documentsMaster2->every(fn ($document): bool => (bool) $document->obligatoire),
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function attentesLicence(): array
    {
        return [
            'licence_1' => [
                'cni_passeport',
                'diplome',
                'releve_notes',
                'releve_notes_terminale',
                'lettre_motivation',
            ],
            'licence_2' => [
                'cni_passeport',
                'diplome',
                'releve_notes_licence_1',
                'lettre_motivation',
            ],
            'licence_3' => [
                'cni_passeport',
                'diplome',
                'releve_notes_licence_1',
                'releve_notes_licence_2',
                'lettre_motivation',
            ],
        ];
    }
}
