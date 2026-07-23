<?php

namespace App\Policies;

use App\Enums\CandidatureStatut;
use App\Enums\RoleUtilisateur;
use App\Models\Candidature;
use App\Models\User;

class CandidaturePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            RoleUtilisateur::ADMISSION,
            RoleUtilisateur::SUPER_ADMIN,
        ], true);
    }

    public function view(User $user, Candidature $candidature): bool
    {
        if ($user->role === RoleUtilisateur::SUPER_ADMIN) {
            return true;
        }

        return $user->role === RoleUtilisateur::ADMISSION
            && $candidature->statut !== CandidatureStatut::BROUILLON;
    }

    public function prendreEnCharge(User $user, Candidature $candidature): bool
    {
        return $user->role === RoleUtilisateur::ADMISSION
            && $candidature->statut === CandidatureStatut::SOUMISE
            && $candidature->agent_admission_id === null;
    }

    public function controlerDocuments(User $user, Candidature $candidature): bool
    {
        return $user->role === RoleUtilisateur::ADMISSION
            && $candidature->statut === CandidatureStatut::EN_TRAITEMENT_ADMISSION
            && $candidature->agent_admission_id === $user->id;
    }

    public function transmettreAuJury(User $user, Candidature $candidature): bool
    {
        return $this->controlerDocuments($user, $candidature);
    }

    public function demanderComplement(User $user, Candidature $candidature): bool
    {
        return $this->controlerDocuments($user, $candidature);
    }
}
