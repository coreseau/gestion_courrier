<?php

namespace App\Policies;

use App\Models\Dossier;
use App\Models\User;

class DossierPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dossier $dossier): bool
    {
        return Dossier::visiblesPour($user)->whereKey($dossier->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['directeur', 'admin', 'collaborateur']);
    }
    public function coter(User $user, Dossier $dossier): bool
{
    return $user->hasAnyRole(['directeur', 'admin'])
        && in_array($dossier->statut, [
            Dossier::STATUT_RECU,
            Dossier::STATUT_COTE,
            Dossier::STATUT_EN_TRAITEMENT,
            Dossier::STATUT_A_REPRENDRE,
        ]);
}

    public function transferer(User $user, Dossier $dossier): bool
    {
        return $user->hasRole('collaborateur')
            && in_array($dossier->statut, [
                Dossier::STATUT_COTE,
                Dossier::STATUT_EN_TRAITEMENT,
                Dossier::STATUT_A_REPRENDRE,
            ])
            && $dossier->estCoteActif($user);
    }
        public function traiter(User $user, Dossier $dossier): bool
    {
        return $user->hasRole('collaborateur')
            && in_array($dossier->statut, [
                Dossier::STATUT_COTE,
                Dossier::STATUT_EN_TRAITEMENT,
                Dossier::STATUT_A_REPRENDRE,
            ])
            && $dossier->estCoteActif($user);
    }

    public function decider(User $user, Dossier $dossier): bool
    {
        return $user->hasAnyRole(['directeur', 'admin'])
            && $dossier->statut === Dossier::STATUT_SOUMIS;
    }

    public function cloturer(User $user, Dossier $dossier): bool
    {
        return $user->hasAnyRole(['directeur', 'admin'])
            && in_array($dossier->statut, [Dossier::STATUT_VALIDE, Dossier::STATUT_RECU]);
    }
}