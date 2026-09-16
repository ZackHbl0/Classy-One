<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only Admin can view audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->isAdmin();
    }

    /**
     * Audit logs are immutable - no creation via standard UI.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Audit logs are immutable - no modification allowed.
     */
    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Audit logs are immutable - strictly no deletion allowed to preserve trail.
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
