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

    public function libelle(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMISE => 'Soumise',
            self::EN_TRAITEMENT_ADMISSION => 'En traitement admission',
            self::COMPLEMENT_ADMISSION => 'Complément demandé par l’admission',
            self::TRANSMISE_AU_JURY => 'Transmise au jury',
            self::COMPLEMENT_JURY => 'Complément demandé par le jury',
            self::ADMISE => 'Admise',
            self::REFUSEE => 'Refusée',
        };
    }

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

    /**
     * Statuts des dossiers qui ont atteint l'étape du jury.
     *
     * @return list<self>
     */
    public static function visiblesParJury(): array
    {
        return [
            self::TRANSMISE_AU_JURY,
            self::COMPLEMENT_JURY,
            self::ADMISE,
            self::REFUSEE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function valeursVisiblesParJury(): array
    {
        return array_map(
            fn (self $statut): string => $statut->value,
            self::visiblesParJury(),
        );
    }
}
