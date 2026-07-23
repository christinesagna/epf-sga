<?php

namespace Tests\Feature\Candidature;

use App\Enums\CandidatureStatut;
use App\Livewire\Candidature\Formulaire;
use App\Mail\CandidatureSoumiseMail;
use App\Models\Candidature;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use Database\Seeders\NiveauxSeeder;
use Database\Seeders\ProgrammesSeeder;
use Database\Seeders\TypesDocumentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FormulaireCandidatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);
    }

    public function test_le_candidat_choisit_explicitement_un_niveau_du_programme(): void
    {
        $programme = Programme::query()
            ->where('nom', 'Licence Concepteur de systemes d information')
            ->with('niveaux.niveau')
            ->firstOrFail();

        Livewire::test(Formulaire::class)
            ->set('step', 3)
            ->set('serie_baccalaureat', 'S')
            ->set('programme_id', $programme->id)
            ->assertSet('programme_niveau_id', null)
            ->assertSee('Licence 1')
            ->assertSee('Licence 2')
            ->assertSee('Licence 3')
            ->set('programme_niveau_id', $programme->niveaux
                ->firstWhere('niveau.code', 'licence_2')
                ->id)
            ->assertSet('programme_niveau_id', $programme->niveaux
                ->firstWhere('niveau.code', 'licence_2')
                ->id);
    }

    public function test_la_soumission_refuse_les_documents_obligatoires_manquants(): void
    {
        [$programme, $programmeNiveau] = $this->programmeEtNiveau('licence_1');

        $this->formulaireComplet($programme->id, $programmeNiveau->id)
            ->call('save')
            ->assertHasErrors([
                'documents.cni_passeport',
                'documents.diplome',
                'documents.releve_notes',
                'documents.releve_notes_terminale',
                'documents.lettre_motivation',
            ]);

        $this->assertDatabaseCount('candidatures', 0);
    }

    public function test_la_soumission_enregistre_le_niveau_et_stocke_les_documents_en_prive(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Mail::fake();

        [$programme, $programmeNiveau] = $this->programmeEtNiveau('licence_1');
        $composant = $this->formulaireComplet($programme->id, $programmeNiveau->id);

        foreach ($programmeNiveau->typesDocuments as $document) {
            $composant->set(
                'documents.'.$document->code,
                UploadedFile::fake()->create($document->code.'.pdf', 100, 'application/pdf'),
            );
        }

        $composant
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $candidature = Candidature::query()->with('documents')->sole();

        $this->assertSame($programme->id, $candidature->programme_id);
        $this->assertSame($programmeNiveau->id, $candidature->programme_niveau_id);
        $this->assertSame(CandidatureStatut::SOUMISE, $candidature->statut);
        $this->assertCount($programmeNiveau->typesDocuments->count(), $candidature->documents);

        foreach ($candidature->documents as $document) {
            Storage::disk('local')->assertExists($document->path);
            Storage::disk('public')->assertMissing($document->path);
        }

        Mail::assertSent(
            CandidatureSoumiseMail::class,
            fn (CandidatureSoumiseMail $mail): bool => $mail->hasTo('candidat@gmail.com')
                && $mail->candidature->is($candidature),
        );

        $mail = Mail::sent(CandidatureSoumiseMail::class)->first();

        $this->assertStringContainsString(
            route('candidatures.suivi', [$candidature, $candidature->edit_token]),
            $mail->render(),
        );
    }

    public function test_un_niveau_d_un_autre_programme_est_refuse(): void
    {
        [$licence] = $this->programmeEtNiveau('licence_1');
        [, $niveauMaster] = $this->programmeEtNiveau('master_1', 'Master Informatique');

        $this->formulaireComplet($licence->id, $niveauMaster->id)
            ->call('save')
            ->assertHasErrors(['programme_niveau_id']);

        $this->assertDatabaseCount('candidatures', 0);
    }

    public function test_une_candidature_existante_n_est_pas_ecrasee_par_le_formulaire_initial(): void
    {
        Storage::fake('local');
        [$programme, $programmeNiveau] = $this->programmeEtNiveau('licence_1');
        $composant = $this->formulaireComplet($programme->id, $programmeNiveau->id);

        foreach ($programmeNiveau->typesDocuments as $document) {
            $composant->set(
                'documents.'.$document->code,
                UploadedFile::fake()->create($document->code.'.pdf', 100, 'application/pdf'),
            );
        }

        $composant->call('save')->assertHasNoErrors();
        $premiereCandidature = Candidature::query()->sole();

        $nouvelleTentative = $this->formulaireComplet($programme->id, $programmeNiveau->id);

        foreach ($programmeNiveau->typesDocuments as $document) {
            $nouvelleTentative->set(
                'documents.'.$document->code,
                UploadedFile::fake()->create('remplacement-'.$document->code.'.pdf', 100, 'application/pdf'),
            );
        }

        $nouvelleTentative
            ->call('save')
            ->assertHasErrors(['programme_id']);

        $this->assertDatabaseCount('candidatures', 1);
        $this->assertTrue($premiereCandidature->is(Candidature::query()->sole()));
    }

    public function test_le_catalogue_public_affiche_uniquement_les_programmes_actifs_de_la_base(): void
    {
        Programme::query()->create([
            'nom' => 'Ancienne licence',
            'slug' => 'ancienne-licence',
            'niveau' => 'licence',
            'capacite_accueil' => 50,
            'description' => 'Programme désactivé.',
            'actif' => false,
        ]);

        $this->get(route('programmes.show', 'licences'))
            ->assertOk()
            ->assertSee('Licence Concepteur de systemes d information')
            ->assertSee('Licence Management de la transition numerique')
            ->assertSee('Licence Energie et environnement')
            ->assertDontSee('Ancienne licence');
    }

    /**
     * @return array{0: Programme, 1: ProgrammeNiveau}
     */
    private function programmeEtNiveau(
        string $codeNiveau,
        string $nomProgramme = 'Licence Concepteur de systemes d information',
    ): array {
        $programme = Programme::query()
            ->where('nom', $nomProgramme)
            ->with(['niveaux.niveau', 'niveaux.typesDocuments'])
            ->firstOrFail();
        $programmeNiveau = $programme->niveaux
            ->first(fn ($association) => $association->niveau->code === $codeNiveau);

        $this->assertNotNull($programmeNiveau);

        return [$programme, $programmeNiveau];
    }

    private function formulaireComplet(int $programmeId, int $programmeNiveauId): mixed
    {
        return Livewire::test(Formulaire::class)
            ->set('nom', 'Diop')
            ->set('prenom', 'Aminata')
            ->set('telephone', '+221770000000')
            ->set('date_naissance', '2000-01-01')
            ->set('email', 'candidat@gmail.com')
            ->set('lieu_naissance', 'Dakar')
            ->set('nationalite', 'Sénégalaise')
            ->set('sexe', 'feminin')
            ->set('pays', 'Sénégal')
            ->set('adresse', 'Dakar')
            ->set('dernier_diplome', 'baccalaureat')
            ->set('serie_baccalaureat', 'S')
            ->set('programme_id', $programmeId)
            ->set('programme_niveau_id', $programmeNiveauId);
    }
}
