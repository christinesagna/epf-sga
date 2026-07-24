<?php

namespace Tests\Feature\Jury;

use App\Enums\CandidatureStatut;
use App\Mail\DecisionCandidatureMail;
use App\Mail\DemandeComplementCandidatureMail;
use App\Mail\ReorientationCandidatureMail;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class TraitementCandidaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_jury_demande_un_complement_et_le_candidat_est_prevenu(): void
    {
        Mail::fake();

        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature();
        $typeDocument = $this->associerTypeDocument($candidature);

        $this->actingAs($jury)
            ->post(route('jury.candidatures.demande-complement', $candidature), [
                'type_document_ids' => [$typeDocument->id],
                'motif_complement' => 'Merci de transmettre un relevé de notes plus récent.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(
            CandidatureStatut::COMPLEMENT_JURY,
            $candidature->fresh()->statut,
        );
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'ancien_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'nouveau_statut' => CandidatureStatut::COMPLEMENT_JURY->value,
            'acteur_type' => 'jury',
            'acteur_id' => $jury->id,
            'commentaire' => 'Merci de transmettre un relevé de notes plus récent.',
        ]);
        $historique = $candidature->historiques()->latest()->firstOrFail();
        $this->assertSame([$typeDocument->id], $historique->metadata['type_document_ids']);

        Mail::assertSent(
            DemandeComplementCandidatureMail::class,
            fn (DemandeComplementCandidatureMail $mail): bool => $mail
                ->hasTo($candidature->candidat->email)
                && $mail->origine === 'jury',
        );
    }

    public function test_un_document_hors_du_programme_ne_peut_pas_etre_demande(): void
    {
        Mail::fake();

        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature();
        $documentAutorise = $this->associerTypeDocument($candidature);
        $autreDocument = TypeDocument::query()->create([
            'code' => 'document_hors_programme',
            'libelle' => 'Document hors programme',
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 5,
            'actif' => true,
        ]);

        $this->actingAs($jury)
            ->post(route('jury.candidatures.demande-complement', $candidature), [
                'type_document_ids' => [$documentAutorise->id, $autreDocument->id],
                'motif_complement' => 'Documents attendus.',
            ])
            ->assertSessionHasErrors('type_document_ids');

        $this->assertSame(
            CandidatureStatut::TRANSMISE_AU_JURY,
            $candidature->fresh()->statut,
        );
        Mail::assertNothingSent();
    }

    public function test_le_jury_admet_une_candidature_et_son_identite_est_historisee(): void
    {
        Mail::fake();

        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature();

        $this->actingAs($jury)
            ->post(route('jury.candidatures.decision', $candidature), [
                'decision' => CandidatureStatut::ADMISE->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(CandidatureStatut::ADMISE, $candidature->fresh()->statut);
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'nouveau_statut' => CandidatureStatut::ADMISE->value,
            'acteur_type' => 'jury',
            'acteur_id' => $jury->id,
        ]);
        Mail::assertSent(
            DecisionCandidatureMail::class,
            fn (DecisionCandidatureMail $mail): bool => $mail
                ->hasTo($candidature->candidat->email),
        );

        $this->actingAs($jury)
            ->post(route('jury.candidatures.decision', $candidature), [
                'decision' => CandidatureStatut::REFUSEE->value,
                'motif_decision' => 'Seconde décision interdite.',
            ])
            ->assertForbidden();
    }

    public function test_le_refus_exige_un_motif_et_envoie_la_decision(): void
    {
        Mail::fake();

        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature();

        $this->actingAs($jury)
            ->post(route('jury.candidatures.decision', $candidature), [
                'decision' => CandidatureStatut::REFUSEE->value,
            ])
            ->assertSessionHasErrors('motif_decision');

        $this->assertSame(
            CandidatureStatut::TRANSMISE_AU_JURY,
            $candidature->fresh()->statut,
        );

        $this->actingAs($jury)
            ->post(route('jury.candidatures.decision', $candidature), [
                'decision' => CandidatureStatut::REFUSEE->value,
                'motif_decision' => 'Les prérequis académiques ne sont pas atteints.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(CandidatureStatut::REFUSEE, $candidature->fresh()->statut);
        $this->assertDatabaseHas('candidature_historiques', [
            'candidature_id' => $candidature->id,
            'nouveau_statut' => CandidatureStatut::REFUSEE->value,
            'acteur_id' => $jury->id,
            'commentaire' => 'Les prérequis académiques ne sont pas atteints.',
        ]);
        Mail::assertSent(DecisionCandidatureMail::class);
    }

    public function test_le_jury_reoriente_vers_un_autre_programme_actif(): void
    {
        Mail::fake();

        $jury = User::factory()->jury()->create();
        $candidature = $this->creerCandidature();
        $programmeInitial = $candidature->programme;
        $nouveauNiveau = $this->creerProgrammeNiveau('Programme réorienté');

        $this->actingAs($jury)
            ->post(route('jury.candidatures.reorientation', $candidature), [
                'programme_niveau_id' => $nouveauNiveau->id,
                'motif_reorientation' => 'Ce programme correspond mieux à votre parcours.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $candidature->refresh();

        $this->assertSame($nouveauNiveau->programme_id, $candidature->programme_id);
        $this->assertSame($nouveauNiveau->id, $candidature->programme_niveau_id);
        $this->assertSame($programmeInitial->id, $candidature->programme_origine_id);
        $this->assertSame(CandidatureStatut::TRANSMISE_AU_JURY, $candidature->statut);

        $historique = $candidature->historiques()->latest()->firstOrFail();
        $this->assertSame($jury->id, $historique->acteur_id);
        $this->assertSame('reorientation', $historique->metadata['action']);
        $this->assertSame($programmeInitial->id, $historique->metadata['ancien_programme_id']);
        $this->assertSame($nouveauNiveau->programme_id, $historique->metadata['nouveau_programme_id']);

        Mail::assertSent(
            ReorientationCandidatureMail::class,
            fn (ReorientationCandidatureMail $mail): bool => $mail
                ->hasTo($candidature->candidat->email)
                && $mail->ancienProgramme->is($programmeInitial),
        );

        $this->actingAs($jury)
            ->get(route('jury.candidatures.show', $candidature))
            ->assertOk()
            ->assertSee('Réorientation du dossier')
            ->assertSee($programmeInitial->nom)
            ->assertSee($nouveauNiveau->programme->nom)
            ->assertSee('Ce programme correspond mieux à votre parcours.');
    }

    public function test_l_admission_ne_peut_pas_utiliser_les_actions_du_jury(): void
    {
        Mail::fake();

        $agent = User::factory()->create();
        $candidature = $this->creerCandidature();

        $this->actingAs($agent)
            ->post(route('jury.candidatures.decision', $candidature), [
                'decision' => CandidatureStatut::ADMISE->value,
            ])
            ->assertForbidden();

        $this->assertSame(
            CandidatureStatut::TRANSMISE_AU_JURY,
            $candidature->fresh()->statut,
        );
        Mail::assertNothingSent();
    }

    private function creerCandidature(): Candidature
    {
        $suffixe = Str::lower(Str::random(8));
        $candidat = Candidat::query()->create([
            'nom' => 'Diop',
            'prenom' => 'Aminata',
            'date_naissance' => '2000-01-01',
            'email' => "{$suffixe}@example.test",
            'telephone' => '+221770000000',
            'pays' => 'Sénégal',
            'adresse' => 'Dakar',
            'sexe' => 'feminin',
        ]);
        $programmeNiveau = $this->creerProgrammeNiveau("Programme {$suffixe}");

        return Candidature::query()->create([
            'candidat_id' => $candidat->id,
            'programme_id' => $programmeNiveau->programme_id,
            'programme_niveau_id' => $programmeNiveau->id,
            'edit_token' => (string) Str::uuid(),
            'statut' => CandidatureStatut::TRANSMISE_AU_JURY,
            'derniere_formation' => 'baccalaureat',
            'submitted_at' => now(),
        ]);
    }

    private function creerProgrammeNiveau(string $nom): ProgrammeNiveau
    {
        $suffixe = Str::lower(Str::random(8));
        $programme = Programme::query()->create([
            'nom' => $nom,
            'slug' => "programme-{$suffixe}",
            'niveau' => 'licence',
            'capacite_accueil' => 30,
            'actif' => true,
        ]);
        $niveau = Niveau::query()->create([
            'code' => "niveau_{$suffixe}",
            'libelle' => "Niveau {$suffixe}",
        ]);

        return $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);
    }

    private function associerTypeDocument(Candidature $candidature): TypeDocument
    {
        $typeDocument = TypeDocument::query()->create([
            'code' => 'releve_'.$candidature->id,
            'libelle' => 'Relevé de notes',
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 5,
            'actif' => true,
        ]);
        $candidature->programmeNiveau->typesDocuments()->attach($typeDocument->id, [
            'obligatoire' => true,
            'ordre' => 1,
        ]);

        return $typeDocument;
    }
}
