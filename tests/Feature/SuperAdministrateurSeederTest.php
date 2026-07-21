<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Database\Seeders\SuperAdministrateurSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class SuperAdministrateurSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_seeder_cree_un_super_administrateur_actif_et_verifie_sans_ecraser_son_mot_de_passe(): void
    {
        config()->set('backoffice.super_admin', [
            'name' => 'Administration EPF',
            'email' => 'admin@epf.test',
            'password' => 'Premier-mot-de-passe-2026',
        ]);

        $seeder = new SuperAdministrateurSeeder;
        $seeder->run();

        $user = User::query()->where('email', 'admin@epf.test')->firstOrFail();

        $this->assertSame(RoleUtilisateur::SUPER_ADMIN, $user->role);
        $this->assertTrue($user->actif);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check('Premier-mot-de-passe-2026', $user->password));

        config()->set('backoffice.super_admin.password', 'Mot-de-passe-a-ne-pas-appliquer');
        $seeder->run();

        $this->assertSame(1, User::query()->where('email', 'admin@epf.test')->count());
        $this->assertTrue(Hash::check('Premier-mot-de-passe-2026', $user->fresh()->password));
    }

    public function test_le_seeder_refuse_de_promouvoir_un_compte_existant(): void
    {
        User::factory()->create(['email' => 'admin@epf.test']);

        config()->set('backoffice.super_admin', [
            'name' => 'Administration EPF',
            'email' => 'admin@epf.test',
            'password' => 'Premier-mot-de-passe-2026',
        ]);

        $this->expectException(RuntimeException::class);

        (new SuperAdministrateurSeeder)->run();
    }
}
