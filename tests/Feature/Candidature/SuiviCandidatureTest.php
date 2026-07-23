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
