<?php

namespace Tests\Feature\Jury;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\CandidatureDocument;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsultationCandidaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_espace_jury_est_reserve_aux_membres_du_jury(): void
    {
        $jury = User::factory()->jury()->create();
        $agent = User::factory()->create();
        $administrateur = User::factory()->superAdmin()->create();

        $this->get(route('jury.dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($jury)
            ->get(route('jury.dashboard'))
            ->assertOk()
            ->assertSee('Espace jury');

        $this->actingAs($agent)
            ->get(route('jury.dashboard'))
            ->assertForbidden();

        $this->actingAs($administrateur)
            ->get(route('jury.dashboard'))
            ->assertForbidden();
    }

    public function test_le_tableau_de_bord_compte_uniquement_les_dossiers_arrives_au_jury(): void
    {
        $jury = User::factory()->jury()->create();
        $this->creerCandidature(CandidatureStatut::SOUMISE, 'Invisible');
        $this->creerCandidature(CandidatureStatut::TRANSMISE_AU_JURY, 'À étudier');
        $this->creerCandidature(CandidatureStatut::COMPLEMENT_JURY, 'Complément');
        $this->creerCandidature(CandidatureStatut::ADMISE, 'Admise');
        $this->creerCandidature(CandidatureStatut::REFUSEE, 'Refusée');

        $this->actingAs($jury)
            ->get(route('jury.dashboard'))
            ->assertOk()
            ->assertViewHas('totalDossiers', 4)
            ->assertViewHas('aEtudier', 1)
            ->assertViewHas('complementsEnAttente', 1)
            ->assertViewHas('decisionsRendues', 2)
            ->assertDontSee('Invisible');
    }

    public function test_la_liste_du_jury_est_recherchee_et_filtree_sans_exposer_l_admission(): void
    {
        $jury = User::factory()->jury()->create();
        $dossierAwa = $this->creerCandidature(
            CandidatureStatut::TRANSMISE_AU_JURY,
            'Awa',
            '2026-07-20 10:00:00',
        );
        $this->creerCandidature(
            CandidatureStatut::TRANSMISE_AU_JURY,
            'Moussa',
            '2026-07-10 10:00:00',
        );
        $this->creerCandidature(CandidatureStatut::SOUMISE, 'Invisible');

        $this->actingAs($jury)
            ->get(route('jury.candidatures.index', [
                'recherche' => 'Awa',
                'programme_id' => $dossierAwa->programme_id,
                'statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
                'date_debut' => '2026-07-15',
                'date_fin' => '2026-07-21',
            ]))
            ->assertOk()
            ->assertSee('Awa')
            ->assertDontSee('Moussa')
            ->assertDontSee('Invisible')
            ->assertViewHas('candidatures', fn ($dossiers): bool => $dossiers->total() === 1);

        $this->actingAs($jury)
            ->get(route('jury.candidatures.index', [
                'statut' => CandidatureStatut::SOUMISE->value,
            ]))
            ->assertSessionHasErrors('statut');
    }

    public function test_un_jure_consulte_un_dossier_transmis_mais_pas_un_dossier_d_admission(): void
    {
        $jury = User::factory()->jury()->create();
        $transmise = $this->creerCandidature(CandidatureStatut::TRANSMISE_AU_JURY, 'Fatou');
        $soumise = $this->creerCandidature(CandidatureStatut::SOUMISE, 'Privée');
        $document = $this->ajouterDocument($transmise);

        $transmise->historiques()->create([
            'ancien_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'acteur_type' => 'admission',
            'commentaire' => 'Dossier complet transmis au jury.',
        ]);

        $this->actingAs($jury)
            ->get(route('jury.candidatures.show', $transmise))
            ->assertOk()
            ->assertSee('Fatou')
            ->assertSee($transmise->programme->nom)
            ->assertSee($document->typeDocument->libelle)
            ->assertSee('Dossier complet transmis au jury.')
            ->assertSee('Consultation uniquement');

        $this->actingAs($jury)
            ->get(route('jury.candidatures.show', $soumise))
            ->assertForbidden();
    }

    public function test_l_ouverture_d_un_pdf_prive_est_limitee_aux_dossiers_du_jury(): void
    {
        Storage::fake('local');

        $jury = User::factory()->jury()->create();
        $agent = User::factory()->create();
        $transmise = $this->creerCandidature(CandidatureStatut::TRANSMISE_AU_JURY, 'Mariama');
        $soumise = $this->creerCandidature(CandidatureStatut::SOUMISE, 'Aïssatou');
        $documentJury = $this->ajouterDocument($transmise);
        $documentAdmission = $this->ajouterDocument($soumise);

        Storage::disk('local')->put($documentJury->path, "%PDF-1.4\njury");
        Storage::disk('local')->put($documentAdmission->path, "%PDF-1.4\nadmission");

        $reponse = $this->actingAs($jury)
            ->get(route('jury.documents.show', $documentJury))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith(
            'inline;',
            (string) $reponse->headers->get('content-disposition'),
        );

        $this->actingAs($jury)
            ->get(route('jury.documents.show', $documentAdmission))
            ->assertForbidden();

        $this->actingAs($agent)
            ->get(route('jury.documents.show', $documentJury))
            ->assertForbidden();
    }

    private function creerCandidature(
        CandidatureStatut $statut,
        string $prenom,
        string $submittedAt = '2026-07-23 10:00:00',
    ): Candidature {
        $suffixe = Str::lower(Str::random(8));
        $candidat = Candidat::query()->create([
            'nom' => 'Diop',
            'prenom' => $prenom,
            'date_naissance' => '2000-01-01',
            'email' => "{$suffixe}@example.test",
            'telephone' => '+221770000000',
            'pays' => 'Sénégal',
            'adresse' => 'Dakar',
            'sexe' => 'feminin',
        ]);
        $programme = Programme::query()->create([
            'nom' => "Programme {$suffixe}",
            'slug' => "programme-{$suffixe}",
            'niveau' => 'licence',
            'capacite_accueil' => 30,
            'actif' => true,
        ]);
        $niveau = Niveau::query()->create([
            'code' => "niveau_{$suffixe}",
            'libelle' => "Niveau {$prenom}",
        ]);
        $programmeNiveau = $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);

        return Candidature::query()->create([
            'candidat_id' => $candidat->id,
            'programme_id' => $programme->id,
            'programme_niveau_id' => $programmeNiveau->id,
            'edit_token' => (string) Str::uuid(),
            'statut' => $statut,
            'derniere_formation' => 'baccalaureat',
            'etablissement_provenance' => 'Lycée EPF',
            'motivation' => 'Je souhaite intégrer ce programme.',
            'submitted_at' => $submittedAt,
        ]);
    }

    private function ajouterDocument(Candidature $candidature): CandidatureDocument
    {
        $typeDocument = TypeDocument::query()->create([
            'code' => 'piece_identite_'.$candidature->id,
            'libelle' => 'Pièce d’identité',
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 5,
            'actif' => true,
        ]);

        return CandidatureDocument::query()->create([
            'candidature_id' => $candidature->id,
            'type_document_id' => $typeDocument->id,
            'original_name' => 'piece-identite.pdf',
            'stored_name' => 'piece-identite-'.$candidature->id.'.pdf',
            'path' => 'candidature_documents/piece-identite-'.$candidature->id.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'statut_validation' => DocumentStatutValidation::VALIDE,
        ]);
    }
}
