<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case ADMISSION = 'admission';
    case JURY = 'jury';
    case SUPER_ADMIN = 'super_admin';
}
