<?php

namespace Tests\Feature\Candidature;

use App\Enums\CandidatureStatut;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Niveau;
use App\Models\Programme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuiviCandidatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_lien_personnel_affiche_le_suivi_sans_compte_candidat(): void
    {
        $candidature = $this->creerCandidature();
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::BROUILLON->value,
            'nouveau_statut' => CandidatureStatut::SOUMISE->value,
            'acteur_type' => 'candidat',
            'commentaire' => 'Candidature soumise.',
        ]);

        $this->get(route('candidatures.suivi', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertSee('Bonjour Aminata')
            ->assertSee($candidature->programme->nom)
            ->assertSee('Soumise')
            ->assertSee('Candidature soumise.')
            ->assertDontSee('Votre candidature a été réorientée')
            ->assertDontSee($candidature->candidat->email);
    }

    public function test_un_jeton_incorrect_ne_donne_pas_acces_au_suivi(): void
    {
        $candidature = $this->creerCandidature();

        $this->get(route('candidatures.suivi', [
            $candidature,
            (string) Str::uuid(),
        ]))->assertNotFound();
    }

    public function test_la_page_de_suivi_propose_le_formulaire_quand_un_complement_est_attendu(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::COMPLEMENT_ADMISSION);

        $this->get(route('candidatures.suivi', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertSee('Un complément est attendu')
            ->assertSee(route('candidatures.complements.edit', [
                $candidature,
                $candidature->edit_token,
            ]), false);
    }

    public function test_le_suivi_explique_clairement_la_reorientation_du_dossier(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::TRANSMISE_AU_JURY);
        $programmeOrigine = $candidature->programme;
        $nouveauProgramme = Programme::query()->create([
            'nom' => 'Cycle ingénieur',
            'slug' => 'cycle-ingenieur',
            'niveau' => 'ingenieur',
            'capacite_accueil' => 30,
            'actif' => true,
        ]);
        $nouveauNiveau = Niveau::query()->create([
            'code' => 'ingenieur_1',
            'libelle' => 'Ingénieur 1',
        ]);
        $nouveauProgrammeNiveau = $nouveauProgramme->niveaux()->create([
            'niveau_id' => $nouveauNiveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);

        $candidature->update([
            'programme_origine_id' => $programmeOrigine->id,
            'programme_id' => $nouveauProgramme->id,
            'programme_niveau_id' => $nouveauProgrammeNiveau->id,
        ]);
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'nouveau_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'acteur_type' => 'jury',
            'acteur_id' => 3,
            'commentaire' => 'Ce programme correspond mieux à votre parcours.',
            'metadata' => [
                'action' => 'reorientation',
                'ancien_programme_id' => $programmeOrigine->id,
                'nouveau_programme_id' => $nouveauProgramme->id,
            ],
        ]);

        $this->get(route('candidatures.suivi', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertSee('Votre candidature a été réorientée')
            ->assertSee($programmeOrigine->nom)
            ->assertSee($nouveauProgramme->nom)
            ->assertSee('Décision actuelle')
            ->assertSee('Réorientation')
            ->assertSee('Décision enregistrée le')
            ->assertSee('Réorientation du dossier')
            ->assertSee('Ce programme correspond mieux à votre parcours.')
            ->assertSee('Le jury a rendu sa décision d’orientation.')
            ->assertDontSee('Votre dossier reste transmis au jury');
    }

    private function creerCandidature(
        CandidatureStatut $statut = CandidatureStatut::SOUMISE,
    ): Candidature {
        $candidat = Candidat::query()->create([
            'nom' => 'Diop',
            'prenom' => 'Aminata',
            'date_naissance' => '2000-01-01',
            'email' => 'aminata@example.test',
            'telephone' => '+221770000000',
            'pays' => 'Sénégal',
            'adresse' => 'Dakar',
            'sexe' => 'feminin',
        ]);
        $programme = Programme::query()->create([
            'nom' => 'Licence Informatique',
            'slug' => 'licence-informatique',
            'niveau' => 'licence',
            'capacite_accueil' => 30,
            'actif' => true,
        ]);
        $niveau = Niveau::query()->create([
            'code' => 'licence_1',
            'libelle' => 'Licence 1',
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
            'submitted_at' => now(),
            'locked_identity_at' => now(),
        ]);
    }
}
