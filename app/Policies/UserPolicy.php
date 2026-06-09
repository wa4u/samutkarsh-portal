<?php

namespace App\Policies;

use App\Models\User;

/**
 * User management is Trust-Admin-only: no other role holds *_user permissions,
 * and Trust Admin bypasses via Gate::before.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('view_user');
    }

    public function create(User $user): bool
    {
        return $user->can('create_user');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('update_user');
    }

    public function delete(User $user, User $model): bool
    {
        // Never allow deleting your own account from the panel.
        return $user->can('delete_user') && $user->id !== $model->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_user');
    }
}
