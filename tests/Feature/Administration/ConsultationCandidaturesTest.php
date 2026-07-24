<?php

namespace Tests\Feature\Administration;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsultationCandidaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_consultation_est_reservee_aux_super_administrateurs(): void
    {
        $jury = User::factory()->jury()->create();
        $admission = User::factory()->create();
        $administrateur = User::factory()->superAdmin()->create();

        $this->get(route('administration.candidatures.index'))
            ->assertRedirect(route('login'));
        $this->actingAs($jury)
            ->get(route('administration.candidatures.index'))
            ->assertForbidden();
        $this->actingAs($admission)
            ->get(route('administration.candidatures.index'))
            ->assertForbidden();
        $this->actingAs($administrateur)
            ->get(route('administration.candidatures.index'))
            ->assertOk()
            ->assertSee('Toutes les candidatures')
            ->assertSee('lecture seule');
    }

    public function test_la_liste_exclut_les_brouillons_et_applique_les_filtres(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $agent = User::factory()->create(['name' => 'Agent Admission']);
        [$programme, $programmeNiveau] = $this->creerProgrammeNiveau();
        $candidatureVisible = $this->creerCandidature(
            $programme,
            $programmeNiveau,
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Aminata',
            $agent,
        );
        $this->creerCandidature(
            $programme,
            $programmeNiveau,
            CandidatureStatut::SOUMISE,
            'Fatou',
        );
        $brouillon = $this->creerCandidature(
            $programme,
            $programmeNiveau,
            CandidatureStatut::BROUILLON,
            'Brouillon',
        );

        $this->actingAs($administrateur)
            ->get(route('administration.candidatures.index', [
                'recherche' => $candidatureVisible->code_suivi,
                'programme_id' => $programme->id,
                'programme_niveau_id' => $programmeNiveau->id,
                'agent_admission_id' => $agent->id,
                'statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            ]))
            ->assertOk()
            ->assertSee('Aminata')
            ->assertDontSee('Fatou')
            ->assertDontSee('Brouillon');

        $this->actingAs($administrateur)
            ->get(route('administration.candidatures.show', $brouillon))
            ->assertNotFound();
    }

    public function test_le_detail_presente_le_dossier_les_documents_et_l_historique_sans_action_metier(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $agent = User::factory()->create(['name' => 'Agent responsable']);
        [$programme, $programmeNiveau] = $this->creerProgrammeNiveau();
        $candidature = $this->creerCandidature(
            $programme,
            $programmeNiveau,
            CandidatureStatut::TRANSMISE_AU_JURY,
            'Awa',
            $agent,
        );
        $typeDocument = TypeDocument::query()->create([
            'code' => 'diplome',
            'libelle' => 'Diplôme',
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 5,
            'actif' => true,
        ]);
        $candidature->documents()->create([
            'type_document_id' => $typeDocument->id,
            'original_name' => 'diplome.pdf',
            'stored_name' => 'diplome-stocke.pdf',
            'path' => 'candidature_documents/diplome-stocke.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
            'statut_validation' => DocumentStatutValidation::VALIDE,
        ]);
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'acteur_type' => 'admission',
            'acteur_id' => $agent->id,
            'commentaire' => 'Dossier complet transmis au jury.',
        ]);

        $this->actingAs($administrateur)
            ->get(route('administration.candidatures.show', $candidature))
            ->assertOk()
            ->assertSee('Awa')
            ->assertSee('Diplôme')
            ->assertSee('Dossier complet transmis au jury.')
            ->assertSee('Agent responsable')
            ->assertSee('lecture seule', escape: false)
            ->assertDontSee('Prendre en charge')
            ->assertDontSee('Transmettre au jury')
            ->assertDontSee('Demander un complément')
            ->assertDontSee('Admettre la candidature');
    }

    public function test_un_super_administrateur_peut_ouvrir_un_document_prive(): void
    {
        Storage::fake('local');

        $administrateur = User::factory()->superAdmin()->create();
        $jury = User::factory()->jury()->create();
        [$programme, $programmeNiveau] = $this->creerProgrammeNiveau();
        $candidature = $this->creerCandidature(
            $programme,
            $programmeNiveau,
            CandidatureStatut::SOUMISE,
            'Mariama',
        );
        $typeDocument = TypeDocument::query()->create([
            'code' => 'cni',
            'libelle' => 'CNI',
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 5,
            'actif' => true,
        ]);
        Storage::disk('local')->put('candidature_documents/cni.pdf', 'contenu');
        $document = $candidature->documents()->create([
            'type_document_id' => $typeDocument->id,
            'original_name' => 'cni.pdf',
            'stored_name' => 'cni.pdf',
            'path' => 'candidature_documents/cni.pdf',
            'mime_type' => 'application/pdf',
            'size' => 7,
            'statut_validation' => DocumentStatutValidation::EN_ATTENTE,
        ]);

        $this->actingAs($administrateur)
            ->get(route('administration.candidature-documents.show', $document))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($jury)
            ->get(route('administration.candidature-documents.show', $document))
            ->assertForbidden();
    }

    /**
     * @return array{0: Programme, 1: ProgrammeNiveau}
     */
    private function creerProgrammeNiveau(): array
    {
        $programme = Programme::query()->create([
            'nom' => 'Master Administration',
            'slug' => 'master-administration',
            'niveau' => 'master',
            'actif' => true,
        ]);
        $niveau = Niveau::query()->create([
            'code' => 'master_1',
            'libelle' => 'Master 1',
        ]);
        $programmeNiveau = $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);

        return [$programme, $programmeNiveau];
    }

    private function creerCandidature(
        Programme $programme,
        ProgrammeNiveau $programmeNiveau,
        CandidatureStatut $statut,
        string $prenom,
        ?User $agent = null,
    ): Candidature {
        $candidat = Candidat::query()->create([
            'nom' => 'Test',
            'prenom' => $prenom,
            'email' => Str::slug($prenom).'@example.com',
        ]);

        $candidature = Candidature::query()->create([
            'candidat_id' => $candidat->id,
            'programme_id' => $programme->id,
            'programme_niveau_id' => $programmeNiveau->id,
            'agent_admission_id' => $agent?->id,
            'edit_token' => (string) Str::uuid(),
            'statut' => $statut,
            'submitted_at' => $statut === CandidatureStatut::BROUILLON ? null : now(),
        ]);

        $candidature->forceFill([
            'code_suivi' => strtoupper(Str::random(12)),
        ])->save();

        return $candidature;
    }
}
