<?php

namespace App\Enums;

enum DocumentStatutValidation: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDE = 'valide';
    case REJETE = 'rejete';
}
