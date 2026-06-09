<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\User;

/**
 * No role except Trust Admin holds any *_center permission, and Trust Admin
 * bypasses these checks via Gate::before. So Centers are effectively
 * Trust-Admin-only — every method below returns false for everyone else.
 */
class CenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_center');
    }

    public function view(User $user, Center $center): bool
    {
        return $user->can('view_center');
    }

    public function create(User $user): bool
    {
        return $user->can('create_center');
    }

    public function update(User $user, Center $center): bool
    {
        return $user->can('update_center');
    }

    public function delete(User $user, Center $center): bool
    {
        return $user->can('delete_center');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_center');
    }
}
