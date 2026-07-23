<?php

namespace Tests\Feature\Admission;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Mail\DemandeComplementCandidatureMail;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\CandidatureDocument;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsultationCandidaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_agent_d_admission_est_redirige_vers_son_espace_apres_connexion(): void
    {
        $agent = User::factory()->create([
            'email' => 'admission@epf.test',
        ]);

        $this->post(route('login'), [
            'email' => $agent->email,
            'password' => 'password',
        ])->assertRedirect(route('admission.dashboard'));
    }

    public function test_l_espace_admission_est_reserve_aux_roles_autorises(): void
    {
        $agent = User::factory()->create();
        $jury = User::factory()->jury()->create();
        $administrateur = User::factory()->superAdmin()->create();

        $this->get(route('admission.dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($jury)
            ->get(route('admission.dashboard'))
            ->assertForbidden();

        $this->actingAs($agent)
            ->get(route('admission.dashboard'))
            ->assertOk()
            ->assertSee('Service d’admission');

        $this->actingAs($administrateur)
            ->get(route('admission.dashboard'))
            ->assertOk();
    }

    public function test_le_tableau_de_bord_compte_uniquement_les_candidatures_recues(): void
    {
        $agent = User::factory()->create();
        $this->creerCandidature(CandidatureStatut::BROUILLON, 'Brouillon');
        $this->creerCandidature(CandidatureStatut::SOUMISE, 'Soumise');
        $this->creerCandidature(CandidatureStatut::EN_TRAITEMENT_ADMISSION, 'Traitement', $agent);
        $this->creerCandidature(CandidatureStatut::COMPLEMENT_ADMISSION, 'Complément');

        $this->actingAs($agent)
            ->get(route('admission.dashboard'))
            ->assertOk()
            ->assertViewHas('candidaturesRecues', 3)
            ->assertViewHas('nouvellesCandidatures', 1)
            ->assertViewHas('dossiersEnTraitement', 1)
            ->assertViewHas('complementsEnAttente', 1)
            ->assertViewHas('mesDossiers', 1)
            ->assertDontSee('Brouillon');
    }

    public function test_la_liste_peut_etre_recherchee_et_filtree_sans_afficher_les_brouillons(): void
    {
        $agent = User::factory()->create();
        $candidatureAminata = $this->creerCandidature(
            CandidatureStatut::SOUMISE,
            'Aminata',
            submittedAt: '2026-07-20 10:00:00',
        );
        $candidatureMoussa = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Moussa',
            submittedAt: '2026-07-10 10:00:00',
        );
        $this->creerCandidature(CandidatureStatut::BROUILLON, 'Invisible');

        $this->actingAs($agent)
            ->get(route('admission.candidatures.index', [
                'recherche' => 'Aminata',
                'programme_id' => $candidatureAminata->programme_id,
                'statut' => CandidatureStatut::SOUMISE->value,
                'date_debut' => '2026-07-15',
                'date_fin' => '2026-07-21',
            ]))
            ->assertOk()
            ->assertSee('Aminata')
            ->assertDontSee('Moussa')
            ->assertDontSee('Invisible')
            ->assertViewHas('candidatures', fn ($candidatures): bool => $candidatures->total() === 1);

        $this->actingAs($agent)
            ->get(route('admission.candidatures.index'))
            ->assertOk()
            ->assertSee('Aminata')
            ->assertSee('Moussa')
            ->assertDontSee('Invisible')
            ->assertViewHas('candidatures', fn ($candidatures): bool => $candidatures->total() === 2);

        $this->assertNotSame($candidatureAminata->programme_id, $candidatureMoussa->programme_id);
    }

    public function test_un_agent_consulte_le_detail_soumis_mais_pas_un_brouillon(): void
    {
        $agent = User::factory()->create();
        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature(CandidatureStatut::SOUMISE, 'Awa');
        $brouillon = $this->creerCandidature(CandidatureStatut::BROUILLON, 'Privé');
        $document = $this->ajouterDocument($candidature);
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::BROUILLON->value,
            'nouveau_statut' => CandidatureStatut::SOUMISE->value,
            'acteur_type' => 'candidat',
            'commentaire' => 'Candidature soumise.',
        ]);

        $this->actingAs($agent)
            ->get(route('admission.candidatures.show', $candidature))
            ->assertOk()
            ->assertSee('Awa')
            ->assertSee($candidature->programme->nom)
            ->assertSee($document->typeDocument->libelle)
            ->assertSee('Candidature soumise.')
            ->assertDontSee('Ouvrir le contrôle')
            ->assertDontSee('min-w-[1050px]', false);

        $this->actingAs($agent)
            ->get(route('admission.candidatures.show', $brouillon))
            ->assertForbidden();

        $this->actingAs($jury)
            ->get(route('admission.candidatures.show', $candidature))
            ->assertForbidden();
    }

    public function test_l_ouverture_d_un_document_prive_est_controlee_par_la_policy(): void
    {
        Storage::fake('local');

        $agent = User::factory()->create();
        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature(CandidatureStatut::SOUMISE, 'Fatou');
        $document = $this->ajouterDocument($candidature);
        $document->update(['mime_type' => 'application/octet-stream']);
        Storage::disk('local')->put($document->path, "%PDF-1.4\ncontenu du document");

        $reponse = $this->actingAs($agent)
            ->get(route('admission.documents.show', $document))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith(
            'inline;',
            (string) $reponse->headers->get('content-disposition'),
        );

        $this->actingAs($jury)
            ->get(route('admission.documents.show', $document))
            ->assertForbidden();
    }

    public function test_un_agent_peut_prendre_en_charge_une_candidature_soumise(): void
    {
        $agent = User::factory()->create();
        $candidature = $this->creerCandidature(CandidatureStatut::SOUMISE, 'Aïssatou');

        $this->actingAs($agent)
            ->post(route('admission.candidatures.prise-en-charge', $candidature))
            ->assertRedirect()
            ->assertSessionHas('status');

        $candidature->refresh();

        $this->assertSame($agent->id, $candidature->agent_admission_id);
        $this->assertSame(CandidatureStatut::EN_TRAITEMENT_ADMISSION, $candidature->statut);
        $this->assertNotNull($candidature->pris_en_charge_at);
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'ancien_statut' => CandidatureStatut::SOUMISE->value,
            'nouveau_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'acteur_type' => 'admission',
            'acteur_id' => $agent->id,
        ]);
    }

    public function test_un_dossier_deja_attribue_ne_peut_pas_etre_pris_par_un_autre_agent(): void
    {
        $agentResponsable = User::factory()->create();
        $autreAgent = User::factory()->create();
        $candidature = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Mariama',
            $agentResponsable,
        );

        $this->actingAs($autreAgent)
            ->post(route('admission.candidatures.prise-en-charge', $candidature))
            ->assertForbidden();

        $this->assertSame($agentResponsable->id, $candidature->fresh()->agent_admission_id);
    }

    public function test_seul_l_agent_responsable_peut_controler_un_document(): void
    {
        $agentResponsable = User::factory()->create();
        $autreAgent = User::factory()->create();
        $candidature = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Ndeye',
            $agentResponsable,
        );
        $document = $this->ajouterDocument($candidature);

        $this->actingAs($agentResponsable)
            ->get(route('admission.candidatures.show', $candidature))
            ->assertOk()
            ->assertSee('data-document-decision', false)
            ->assertSee('data-rejection-reason', false)
            ->assertDontSee('Ouvrir le contrôle');

        $this->actingAs($autreAgent)
            ->patch(route('admission.documents.update', $document), [
                'statut_validation' => DocumentStatutValidation::VALIDE->value,
            ])
            ->assertForbidden();

        $this->actingAs($agentResponsable)
            ->patch(route('admission.documents.update', $document), [
                'statut_validation' => DocumentStatutValidation::VALIDE->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(
            DocumentStatutValidation::VALIDE,
            $document->fresh()->statut_validation,
        );
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'acteur_type' => 'admission',
            'acteur_id' => $agentResponsable->id,
        ]);
    }

    public function test_le_rejet_d_un_document_exige_un_motif(): void
    {
        $agent = User::factory()->create();
        $candidature = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Coumba',
            $agent,
        );
        $document = $this->ajouterDocument($candidature);

        $this->actingAs($agent)
            ->from(route('admission.candidatures.show', $candidature))
            ->patch(route('admission.documents.update', $document), [
                'statut_validation' => DocumentStatutValidation::REJETE->value,
                'commentaire_validation' => '',
            ])
            ->assertRedirect(route('admission.candidatures.show', $candidature))
            ->assertSessionHasErrors('commentaire_validation');

        $this->assertSame(
            DocumentStatutValidation::EN_ATTENTE,
            $document->fresh()->statut_validation,
        );
    }

    public function test_la_transmission_au_jury_exige_tous_les_documents_obligatoires_valides(): void
    {
        $agent = User::factory()->create();
        $candidature = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Rama',
            $agent,
        );
        $document = $this->ajouterDocument($candidature);

        $this->actingAs($agent)
            ->post(route('admission.candidatures.transmission-jury', $candidature))
            ->assertSessionHasErrors('transmission');

        $this->assertSame(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            $candidature->fresh()->statut,
        );

        $document->update([
            'statut_validation' => DocumentStatutValidation::VALIDE,
        ]);

        $this->actingAs($agent)
            ->post(route('admission.candidatures.transmission-jury', $candidature))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(
            CandidatureStatut::TRANSMISE_AU_JURY,
            $candidature->fresh()->statut,
        );
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'ancien_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'acteur_type' => 'admission',
            'acteur_id' => $agent->id,
        ]);
    }

    public function test_l_agent_responsable_demande_un_complement_et_le_candidat_est_prevenu(): void
    {
        Mail::fake();

        $agent = User::factory()->create();
        $candidature = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Astou',
            $agent,
        );
        $document = $this->ajouterDocument($candidature);
        $document->update([
            'statut_validation' => DocumentStatutValidation::REJETE,
            'commentaire_validation' => 'Le document est illisible.',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('admission.candidatures.demande-complement', $candidature), [
                'motif_complement' => 'Tentative par un autre agent.',
            ])
            ->assertForbidden();

        $this->actingAs($agent)
            ->post(route('admission.candidatures.demande-complement', $candidature), [
                'motif_complement' => 'Merci de transmettre une copie lisible de votre pièce d’identité.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(
            CandidatureStatut::COMPLEMENT_ADMISSION,
            $candidature->fresh()->statut,
        );
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'ancien_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
            'nouveau_statut' => CandidatureStatut::COMPLEMENT_ADMISSION->value,
            'acteur_type' => 'admission',
            'acteur_id' => $agent->id,
            'commentaire' => 'Merci de transmettre une copie lisible de votre pièce d’identité.',
        ]);
        $historique = $candidature->historiques()
            ->where('nouveau_statut', CandidatureStatut::COMPLEMENT_ADMISSION->value)
            ->latest()
            ->firstOrFail();
        $this->assertSame(
            [$document->type_document_id],
            $historique->metadata['type_document_ids'],
        );
        Mail::assertSent(
            DemandeComplementCandidatureMail::class,
            fn (DemandeComplementCandidatureMail $mail): bool => $mail->hasTo($candidature->candidat->email)
                && $mail->candidature->is($candidature),
        );
        $mail = Mail::sent(DemandeComplementCandidatureMail::class)->first();
        $this->assertStringContainsString(
            'Merci de transmettre une copie lisible de votre pièce d’identité.',
            $mail->render(),
        );
        $this->assertStringContainsString(
            route('candidatures.suivi', [$candidature, $candidature->edit_token]),
            $mail->render(),
        );

        $this->get(route('candidatures.suivi', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertSee('Merci de transmettre une copie lisible de votre pièce d’identité.')
            ->assertSee('Ajouter les documents');
    }

    public function test_une_demande_de_complement_est_refusee_sans_document_rejete_ou_manquant(): void
    {
        Mail::fake();

        $agent = User::factory()->create();
        $candidature = $this->creerCandidature(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            'Adama',
            $agent,
        );
        $document = $this->ajouterDocument($candidature);
        $document->update([
            'statut_validation' => DocumentStatutValidation::VALIDE,
        ]);

        $this->actingAs($agent)
            ->post(route('admission.candidatures.demande-complement', $candidature), [
                'motif_complement' => 'Merci de compléter votre dossier.',
            ])
            ->assertSessionHasErrors('complement');

        $this->assertSame(
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            $candidature->fresh()->statut,
        );
        Mail::assertNothingSent();
    }

    public function test_un_membre_du_jury_ne_peut_pas_realiser_les_actions_de_l_admission(): void
    {
        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature(CandidatureStatut::SOUMISE, 'Sokhna');

        $this->actingAs($jury)
            ->post(route('admission.candidatures.prise-en-charge', $candidature))
            ->assertForbidden();
    }

    private function creerCandidature(
        CandidatureStatut $statut,
        string $prenom,
        ?User $agent = null,
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
            'agent_admission_id' => $agent?->id,
            'pris_en_charge_at' => $agent ? now() : null,
            'edit_token' => (string) Str::uuid(),
            'statut' => $statut,
            'derniere_formation' => 'baccalaureat',
            'submitted_at' => $statut === CandidatureStatut::BROUILLON ? null : $submittedAt,
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
        $candidature->programmeNiveau->typesDocuments()->attach($typeDocument->id, [
            'obligatoire' => true,
            'ordre' => 1,
        ]);

        return CandidatureDocument::query()->create([
            'candidature_id' => $candidature->id,
            'type_document_id' => $typeDocument->id,
            'original_name' => 'piece-identite.pdf',
            'stored_name' => 'piece-identite-'.$candidature->id.'.pdf',
            'path' => 'candidature_documents/piece-identite-'.$candidature->id.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'statut_validation' => DocumentStatutValidation::EN_ATTENTE,
        ]);
    }
}
