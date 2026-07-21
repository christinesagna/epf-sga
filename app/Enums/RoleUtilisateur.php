<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case ADMISSION = 'admission';
    case JURY = 'jury';
    case SUPER_ADMIN = 'super_admin';

    public function libelle(): string
    {
        return match ($this) {
            self::ADMISSION => "Service d'admission",
            self::JURY => 'Jury',
            self::SUPER_ADMIN => 'Super-administrateur',
        };
    }
}
