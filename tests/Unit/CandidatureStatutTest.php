<?php

namespace Tests\Unit;

use App\Enums\CandidatureStatut;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CandidatureStatutTest extends TestCase
{
    #[DataProvider('transitionsAutoriseesProvider')]
    public function test_une_transition_autorisee_est_acceptee(
        CandidatureStatut $statutActuel,
        CandidatureStatut $nouveauStatut,
    ): void {
        $this->assertTrue($statutActuel->peutTransitionnerVers($nouveauStatut));
    }

    public function test_une_transition_interdite_est_refusee(): void
    {
        $this->assertFalse(
            CandidatureStatut::BROUILLON->peutTransitionnerVers(CandidatureStatut::ADMISE),
        );

        $this->assertFalse(
            CandidatureStatut::ADMISE->peutTransitionnerVers(CandidatureStatut::REFUSEE),
        );
    }

    /**
     * @return iterable<string, array{CandidatureStatut, CandidatureStatut}>
     */
    public static function transitionsAutoriseesProvider(): iterable
    {
        yield 'soumission' => [
            CandidatureStatut::BROUILLON,
            CandidatureStatut::SOUMISE,
        ];

        yield 'prise en charge admission' => [
            CandidatureStatut::SOUMISE,
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
        ];

        yield 'complement admission' => [
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            CandidatureStatut::COMPLEMENT_ADMISSION,
        ];

        yield 'retour admission' => [
            CandidatureStatut::COMPLEMENT_ADMISSION,
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
        ];

        yield 'transmission jury' => [
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            CandidatureStatut::TRANSMISE_AU_JURY,
        ];

        yield 'complement jury' => [
            CandidatureStatut::TRANSMISE_AU_JURY,
            CandidatureStatut::COMPLEMENT_JURY,
        ];

        yield 'retour jury' => [
            CandidatureStatut::COMPLEMENT_JURY,
            CandidatureStatut::TRANSMISE_AU_JURY,
        ];

        yield 'admission' => [
            CandidatureStatut::TRANSMISE_AU_JURY,
            CandidatureStatut::ADMISE,
        ];

        yield 'refus' => [
            CandidatureStatut::TRANSMISE_AU_JURY,
            CandidatureStatut::REFUSEE,
        ];
    }
}
