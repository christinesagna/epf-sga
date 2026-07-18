<?php

namespace App\Enums;

enum CandidatureStatut: string
{
    case BROUILLON = 'brouillon';
    case SOUMISE = 'soumise';
    case EN_TRAITEMENT_ADMISSION = 'en_traitement_admission';
    case COMPLEMENT_ADMISSION = 'complement_admission';
    case TRANSMISE_AU_JURY = 'transmise_au_jury';
    case COMPLEMENT_JURY = 'complement_jury';
    case ADMISE = 'admise';
    case REFUSEE = 'refusee';

    /**
     * @return list<self>
     */
    public function transitionsAutorisees(): array
    {
        return match ($this) {
            self::BROUILLON => [self::SOUMISE],
            self::SOUMISE => [self::EN_TRAITEMENT_ADMISSION],
            self::EN_TRAITEMENT_ADMISSION => [
                self::COMPLEMENT_ADMISSION,
                self::TRANSMISE_AU_JURY,
            ],
            self::COMPLEMENT_ADMISSION => [self::EN_TRAITEMENT_ADMISSION],
            self::TRANSMISE_AU_JURY => [
                self::COMPLEMENT_JURY,
                self::ADMISE,
                self::REFUSEE,
            ],
            self::COMPLEMENT_JURY => [self::TRANSMISE_AU_JURY],
            self::ADMISE, self::REFUSEE => [],
        };
    }

    public function peutTransitionnerVers(self $nouveauStatut): bool
    {
        return in_array($nouveauStatut, $this->transitionsAutorisees(), true);
    }
}
