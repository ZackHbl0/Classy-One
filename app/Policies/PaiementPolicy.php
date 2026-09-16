<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin and Secrétaire can view payments.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'secretaire']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Paiement $paiement): bool
    {
        return in_array($user->role, ['admin', 'secretaire']);
    }

    /**
     * Determine whether the user can create models.
     * Admin and Secrétaire can record incoming payments.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'secretaire']);
    }

    /**
     * Determine whether the user can update the model.
     * Admin and Secrétaire can update payment status.
     */
    public function update(User $user, Paiement $paiement): bool
    {
        return in_array($user->role, ['admin', 'secretaire']);
    }

    /**
     * Determine whether the user can delete the model.
     * STRICT: Only Administrator can delete financial records!
     */
    public function delete(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can bulk delete models.
     * STRICT: Only Administrator can bulk delete financial records!
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin();
    }
}
