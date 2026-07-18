<?php

namespace App\Enums;

enum CandidatureStatut: string
{
    case BROUILLON = 'brouillon';
    case SOUMISE = 'soumise';
    case COMPLEMENT_DEMANDE = 'complement_demande';
    case EN_EVALUATION = 'en_evaluation';
    case ENTRETIEN_PLANIFIE = 'entretien_planifie';
    case ADMISE = 'admise';
    case REFUSEE = 'refusee';
    case LISTE_ATTENTE = 'liste_attente';
    case INSCRIPTION_CONFIRMEE = 'inscription_confirmee';
    case ABANDON = 'abandon';
}
