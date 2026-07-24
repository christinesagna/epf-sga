<?php

namespace Tests\Feature\Candidature;

use App\Enums\CandidatureStatut;
use App\Mail\DecisionCandidatureMail;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Niveau;
use App\Models\Programme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LettreAdmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_candidat_admis_telecharge_sa_lettre_avec_son_lien_personnel(): void
    {
        $candidature = $this->creerCandidature(CandidatureStatut::ADMISE);

        $reponse = $this->get(route('candidatures.lettre-admission', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('lettre-admission-'.$candidature->id.'.pdf');

        $this->assertStringStartsWith('%PDF-', $reponse->getContent());

        $this->get(route('candidatures.suivi', [
            $candidature,
            $candidature->edit_token,
        ]))
            ->assertOk()
            ->assertSee('Votre lettre d’admission est disponible')
            ->assertSee(route('candidatures.lettre-admission', [
                $candidature,
                $candidature->edit_token,
            ]));
    }

    public function test_un_mauvais_jeton_ou_un_dossier_non_admis_ne_donne_pas_la_lettre(): void
    {
        $admise = $this->creerCandidature(CandidatureStatut::ADMISE);
        $refusee = $this->creerCandidature(CandidatureStatut::REFUSEE);

        $this->get(route('candidatures.lettre-admission', [
            $admise,
            'jeton-invalide',
        ]))->assertNotFound();

        $this->get(route('candidatures.lettre-admission', [
            $refusee,
            $refusee->edit_token,
        ]))->assertForbidden();

        $this->get(route('candidatures.suivi', [
            $refusee,
            $refusee->edit_token,
        ]))
            ->assertOk()
            ->assertDontSee('Télécharger ma lettre');
    }

    public function test_l_email_d_admission_contient_la_lettre_pdf_mais_pas_l_email_de_refus(): void
    {
        $admise = $this->creerCandidature(CandidatureStatut::ADMISE);
        $refusee = $this->creerCandidature(CandidatureStatut::REFUSEE);
        $mailAdmission = new DecisionCandidatureMail($admise);
        $mailRefus = new DecisionCandidatureMail($refusee, 'Prérequis insuffisants.');

        $piecesJointes = $mailAdmission->attachments();

        $this->assertCount(1, $piecesJointes);
        $this->assertSame(
            'lettre-admission-'.$admise->id.'.pdf',
            $piecesJointes[0]->as,
        );
        $this->assertSame('application/pdf', $piecesJointes[0]->mime);

        $contenu = $piecesJointes[0]->attachWith(
            fn (): null => null,
            fn ($donnees): string => $donnees(),
        );

        $this->assertStringStartsWith('%PDF-', $contenu);
        $this->assertSame([], $mailRefus->attachments());
    }

    private function creerCandidature(CandidatureStatut $statut): Candidature
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
        $programme = Programme::query()->create([
            'nom' => "Licence Informatique {$suffixe}",
            'slug' => "licence-informatique-{$suffixe}",
            'niveau' => 'licence',
            'capacite_accueil' => 30,
            'actif' => true,
        ]);
        $niveau = Niveau::query()->create([
            'code' => "licence_1_{$suffixe}",
            'libelle' => 'Licence 1',
        ]);
        $programmeNiveau = $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);
        $candidature = Candidature::query()->create([
            'candidat_id' => $candidat->id,
            'programme_id' => $programme->id,
            'programme_niveau_id' => $programmeNiveau->id,
            'edit_token' => (string) Str::uuid(),
            'statut' => $statut,
            'derniere_formation' => 'baccalaureat',
            'submitted_at' => now()->subDays(10),
        ]);
        $candidature->historiques()->create([
            'ancien_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
            'nouveau_statut' => $statut->value,
            'acteur_type' => 'jury',
            'acteur_id' => 1,
            'commentaire' => $statut === CandidatureStatut::ADMISE
                ? 'Candidature admise par le jury.'
                : 'Prérequis insuffisants.',
        ]);

        return $candidature;
    }
}
