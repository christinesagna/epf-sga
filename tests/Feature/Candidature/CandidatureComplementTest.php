<?php

namespace Tests\Feature\Candidature;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\CandidatureDocument;
use App\Models\Programme;
use Database\Seeders\NiveauxSeeder;
use Database\Seeders\ProgrammesSeeder;
use Database\Seeders\TypesDocumentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CandidatureComplementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);
        Storage::fake('local');
    }

    public function test_un_lien_avec_un_mauvais_jeton_est_refuse(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_ADMISSION);

        $this->get(route('candidatures.complements.edit', [$candidature, 'mauvais-jeton']))
            ->assertNotFound();
    }

    public function test_un_complement_est_refuse_s_il_n_a_pas_ete_demande(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::SOUMISE);

        $this->get(route('candidatures.complements.edit', [
            $candidature,
            $candidature->edit_token,
        ]))->assertForbidden();
    }

    public function test_un_document_est_remplace_et_le_dossier_repart_vers_l_admission(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_ADMISSION);
        $typeDocument = $candidature->programmeNiveau->typesDocuments->first();
        $ancienChemin = 'candidature_documents/ancien-document.pdf';
        Storage::disk('local')->put($ancienChemin, 'ancien');

        $document = CandidatureDocument::query()->create([
            'candidature_id' => $candidature->id,
            'type_document_id' => $typeDocument->id,
            'original_name' => 'ancien-document.pdf',
            'stored_name' => 'ancien-document.pdf',
            'path' => $ancienChemin,
            'mime_type' => 'application/pdf',
            'size' => 6,
            'statut_validation' => DocumentStatutValidation::REJETE,
            'commentaire_validation' => 'Document illisible.',
        ]);

        $response = $this->post(route('candidatures.complements.update', [
            $candidature,
            $candidature->edit_token,
        ]), [
            'documents' => [
                $typeDocument->code => UploadedFile::fake()
                    ->create('nouveau-document.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('candidatures.complements.confirmation', [
            $candidature,
            $candidature->edit_token,
        ]));

        $candidature->refresh();
        $document->refresh();

        $this->assertSame(CandidatureStatut::EN_TRAITEMENT_ADMISSION, $candidature->statut);
        $this->assertSame(DocumentStatutValidation::EN_ATTENTE, $document->statut_validation);
        $this->assertNull($document->commentaire_validation);
        $this->assertNotSame($ancienChemin, $document->path);
        Storage::disk('local')->assertMissing($ancienChemin);
        Storage::disk('local')->assertExists($document->path);
        $this->assertDatabaseCount('candidature_documents', 1);
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'ancien_statut' => CandidatureStatut::COMPLEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'acteur_type' => 'candidat',
        ]);
    }

    public function test_un_complement_jury_retransmet_le_dossier_au_jury(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_JURY);
        $typeDocument = $candidature->programmeNiveau->typesDocuments->first();

        $this->post(route('candidatures.complements.update', [
            $candidature,
            $candidature->edit_token,
        ]), [
            'documents' => [
                $typeDocument->code => UploadedFile::fake()
                    ->create('complement-jury.pdf', 100, 'application/pdf'),
            ],
        ])->assertRedirect();

        $this->assertSame(
            CandidatureStatut::TRANSMISE_AU_JURY,
            $candidature->fresh()->statut,
        );
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'ancien_statut' => CandidatureStatut::COMPLEMENT_JURY->value,
            'nouveau_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
        ]);
    }

    private function creerCandidature(CandidatureStatut $statut): Candidature
    {
        $programme = Programme::query()
            ->where('nom', 'Licence Concepteur de systemes d information')
            ->with(['niveaux.niveau', 'niveaux.typesDocuments'])
            ->firstOrFail();
        $programmeNiveau = $programme->niveaux
            ->first(fn ($association) => $association->niveau->code === 'licence_1');
        $candidat = Candidat::query()->create([
            'nom' => 'Diop',
            'prenom' => 'Aminata',
            'email' => fake()->unique()->safeEmail(),
        ]);

        return Candidature::query()->create([
            'candidat_id' => $candidat->id,
            'programme_id' => $programme->id,
            'programme_niveau_id' => $programmeNiveau->id,
            'edit_token' => (string) Str::uuid(),
            'statut' => $statut,
        ]);
    }
}
