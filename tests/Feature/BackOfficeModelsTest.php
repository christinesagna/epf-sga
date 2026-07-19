<?php

namespace Tests\Feature;

use App\Enums\CandidatureStatut;
use App\Enums\RoleUtilisateur;
use App\Models\Candidature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackOfficeModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_table_users_contient_les_colonnes_du_back_office(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'role',
            'actif',
            'email_verified_at',
        ]));
    }

    public function test_la_table_candidatures_contient_les_colonnes_de_traitement(): void
    {
        $this->assertTrue(Schema::hasColumns('candidatures', [
            'programme_origine_id',
            'agent_admission_id',
            'pris_en_charge_at',
        ]));
    }

    public function test_le_role_et_l_etat_actif_sont_castes(): void
    {
        $jury = User::factory()->jury()->create();

        $this->assertSame(RoleUtilisateur::JURY, $jury->role);
        $this->assertTrue($jury->actif);
    }

    public function test_la_reorientation_est_reservee_aux_dossiers_transmis_au_jury(): void
    {
        $candidature = new Candidature([
            'statut' => CandidatureStatut::TRANSMISE_AU_JURY,
        ]);

        $this->assertTrue($candidature->peutEtreReorientee());

        $candidature->statut = CandidatureStatut::EN_TRAITEMENT_ADMISSION;

        $this->assertFalse($candidature->peutEtreReorientee());
    }
}
