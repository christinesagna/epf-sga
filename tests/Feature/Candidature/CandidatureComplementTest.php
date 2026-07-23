<?php

namespace Tests\Feature\Candidature;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Mail\ComplementCandidatureRecuMail;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\CandidatureDocument;
use App\Models\Programme;
use App\Models\User;
use Database\Seeders\NiveauxSeeder;
use Database\Seeders\ProgrammesSeeder;
use Database\Seeders\TypesDocumentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
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
        Mail::fake();

        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_ADMISSION);
        $agent = User::factory()->create();
        $candidature->update(['agent_admission_id' => $agent->id]);
        $typeDocument = $candidature->programmeNiveau->typesDocuments->first();
        $autreTypeDocument = $candidature->programmeNiveau->typesDocuments->skip(1)->first();
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
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::COMPLEMENT_ADMISSION->value,
            'acteur_type' => 'admission',
            'acteur_id' => $agent->id,
            'commentaire' => 'Le document est illisible.',
            'metadata' => ['type_document_ids' => [$typeDocument->id]],
        ]);

        $this->get(route('candidatures.complements.edit', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertSee($typeDocument->libelle)
            ->assertDontSee($autreTypeDocument->libelle)
            ->assertSee('Le document est illisible.');

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
        Mail::assertSent(
            ComplementCandidatureRecuMail::class,
            fn (ComplementCandidatureRecuMail $mail): bool => $mail->hasTo($agent->email)
                && $mail->candidature->is($candidature),
        );
        $mail = Mail::sent(ComplementCandidatureRecuMail::class)->first();
        $this->assertStringContainsString(
            route('admission.candidatures.show', $candidature),
            $mail->render(),
        );
    }

    public function test_un_complement_jury_retransmet_le_dossier_au_jury(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_JURY);
        $typeDocument = $candidature->programmeNiveau->typesDocuments->first();
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'nouveau_statut' => CandidatureStatut::COMPLEMENT_JURY->value,
            'acteur_type' => 'jury',
            'commentaire' => 'Document à remplacer.',
            'metadata' => ['type_document_ids' => [$typeDocument->id]],
        ]);

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

    public function test_tous_les_documents_precisement_demandes_doivent_etre_transmis(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_ADMISSION);
        $documentsDemandes = $candidature->programmeNiveau->typesDocuments->take(2)->values();
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::COMPLEMENT_ADMISSION->value,
            'acteur_type' => 'admission',
            'commentaire' => 'Deux documents sont attendus.',
            'metadata' => [
                'type_document_ids' => $documentsDemandes->pluck('id')->all(),
            ],
        ]);

        $this->post(route('candidatures.complements.update', [
            $candidature,
            $candidature->edit_token,
        ]), [
            'documents' => [
                $documentsDemandes[0]->code => UploadedFile::fake()
                    ->create('premier-document.pdf', 100, 'application/pdf'),
            ],
        ])
            ->assertSessionHasErrors('documents.'.$documentsDemandes[1]->code);

        $this->assertSame(
            CandidatureStatut::COMPLEMENT_ADMISSION,
            $candidature->fresh()->statut,
        );
        $this->assertDatabaseCount('candidature_documents', 0);
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
