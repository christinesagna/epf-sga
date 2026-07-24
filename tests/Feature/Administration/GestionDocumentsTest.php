<?php

namespace Tests\Feature\Administration;

use App\Livewire\Candidature\Formulaire;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\TypeDocument;
use App\Models\User;
use Database\Seeders\NiveauxSeeder;
use Database\Seeders\ProgrammesSeeder;
use Database\Seeders\TypesDocumentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_gestion_des_documents_est_reservee_aux_super_administrateurs(): void
    {
        $jury = User::factory()->jury()->create();
        $administrateur = User::factory()->superAdmin()->create();

        $this->get(route('administration.documents.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($jury)
            ->get(route('administration.documents.index'))
            ->assertForbidden();

        $this->actingAs($administrateur)
            ->get(route('administration.documents.index'))
            ->assertOk()
            ->assertSee('Gestion des documents')
            ->assertSee('Créer un type de document');
    }

    public function test_un_type_est_cree_inactif_modifie_et_historise_sans_changer_de_code(): void
    {
        $administrateur = User::factory()->superAdmin()->create();

        $this->actingAs($administrateur)
            ->post(route('administration.documents.store'), [
                'libelle' => 'Attestation de réussite',
                'description' => 'Attestation délivrée par l’établissement.',
                'extensions_autorisees' => ['pdf', 'png'],
                'taille_max_mb' => 8,
            ])
            ->assertSessionHasNoErrors();

        $typeDocument = TypeDocument::query()->sole();
        $codeInitial = $typeDocument->code;

        $this->assertFalse($typeDocument->actif);
        $this->assertSame(['pdf', 'png'], $typeDocument->extensions_autorisees);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'type_document_cree',
            'cible_type' => 'type_document',
            'cible_id' => $typeDocument->id,
        ]);

        $this->actingAs($administrateur)
            ->put(route('administration.documents.update', $typeDocument), [
                'libelle' => 'Attestation officielle',
                'description' => 'Description modifiée.',
                'extensions_autorisees' => ['pdf'],
                'taille_max_mb' => 12,
            ])
            ->assertSessionHasNoErrors();

        $typeDocument->refresh();
        $this->assertSame($codeInitial, $typeDocument->code);
        $this->assertSame('Attestation officielle', $typeDocument->libelle);

        $this->actingAs($administrateur)
            ->patch(route('administration.documents.etat', $typeDocument), ['actif' => true])
            ->assertSessionHasNoErrors();

        $this->assertTrue($typeDocument->fresh()->actif);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'type_document_active',
            'cible_id' => $typeDocument->id,
        ]);
    }

    public function test_la_validation_limite_les_extensions_et_la_taille(): void
    {
        $administrateur = User::factory()->superAdmin()->create();

        $this->actingAs($administrateur)
            ->post(route('administration.documents.store'), [
                'libelle' => 'Document invalide',
                'extensions_autorisees' => ['exe'],
                'taille_max_mb' => 51,
            ])
            ->assertSessionHasErrors([
                'extensions_autorisees.0',
                'taille_max_mb',
            ]);

        $this->assertDatabaseCount('types_documents', 0);
    }

    public function test_la_configuration_d_un_niveau_preserve_les_associations_inactives(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $programmeNiveau = $this->creerProgrammeNiveau();
        $ancienDocument = $this->creerTypeDocument('ancien_document', true);
        $nouveauDocument = $this->creerTypeDocument('nouveau_document', true);
        $documentInactif = $this->creerTypeDocument('document_historique', false);

        $programmeNiveau->typesDocuments()->attach([
            $ancienDocument->id => ['obligatoire' => true, 'ordre' => 1],
            $documentInactif->id => ['obligatoire' => true, 'ordre' => 9],
        ]);

        $this->actingAs($administrateur)
            ->put(route('administration.programme-niveaux.documents.update', $programmeNiveau), [
                'documents' => [
                    $ancienDocument->id => [
                        'selectionne' => false,
                        'obligatoire' => true,
                        'ordre' => 1,
                    ],
                    $nouveauDocument->id => [
                        'selectionne' => true,
                        'obligatoire' => false,
                        'ordre' => 2,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('programme_niveau_type_document', [
            'programme_niveau_id' => $programmeNiveau->id,
            'type_document_id' => $ancienDocument->id,
        ]);
        $this->assertDatabaseHas('programme_niveau_type_document', [
            'programme_niveau_id' => $programmeNiveau->id,
            'type_document_id' => $nouveauDocument->id,
            'obligatoire' => false,
            'ordre' => 2,
        ]);
        $this->assertDatabaseHas('programme_niveau_type_document', [
            'programme_niveau_id' => $programmeNiveau->id,
            'type_document_id' => $documentInactif->id,
            'ordre' => 9,
        ]);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'documents_niveau_modifies',
            'cible_id' => $programmeNiveau->id,
        ]);
    }

    public function test_les_seeders_preservent_les_modifications_administratives(): void
    {
        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $typeDocument = TypeDocument::query()->where('code', 'cni_passeport')->firstOrFail();
        $typeDocument->update([
            'libelle' => 'Pièce administrée',
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 17,
            'actif' => false,
        ]);
        $programmeNiveau = ProgrammeNiveau::query()->firstOrFail();
        $associationRetiree = $programmeNiveau->typesDocuments()->firstOrFail();
        $programmeNiveau->typesDocuments()->detach($associationRetiree->id);

        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $typeDocument->refresh();
        $this->assertSame('Pièce administrée', $typeDocument->libelle);
        $this->assertFalse($typeDocument->actif);
        $this->assertDatabaseMissing('programme_niveau_type_document', [
            'programme_niveau_id' => $programmeNiveau->id,
            'type_document_id' => $associationRetiree->id,
        ]);
    }

    public function test_un_document_desactive_n_est_plus_demande_au_candidat(): void
    {
        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $programme = Programme::query()
            ->where('nom', 'Licence Concepteur de systemes d information')
            ->with(['niveaux.niveau', 'niveaux.typesDocuments'])
            ->firstOrFail();
        $programmeNiveau = $programme->niveaux
            ->first(fn (ProgrammeNiveau $association): bool => $association->niveau->code === 'licence_1');
        $documentDesactive = $programmeNiveau->typesDocuments->firstOrFail();
        $documentDesactive->update(['actif' => false]);

        Livewire::test(Formulaire::class)
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
            ->set('programme_id', $programme->id)
            ->set('programme_niveau_id', $programmeNiveau->id)
            ->call('save')
            ->assertHasNoErrors(['documents.'.$documentDesactive->code]);
    }

    private function creerTypeDocument(string $code, bool $actif): TypeDocument
    {
        return TypeDocument::query()->create([
            'code' => $code,
            'libelle' => str_replace('_', ' ', ucfirst($code)),
            'extensions_autorisees' => ['pdf'],
            'taille_max_mb' => 5,
            'actif' => $actif,
        ]);
    }

    private function creerProgrammeNiveau(): ProgrammeNiveau
    {
        $programme = Programme::query()->create([
            'nom' => 'Programme documentaire',
            'slug' => 'programme-documentaire',
            'niveau' => 'master',
            'actif' => true,
        ]);
        $niveau = Niveau::query()->create([
            'code' => 'master_documentaire',
            'libelle' => 'Master documentaire',
        ]);

        return $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);
    }
}
