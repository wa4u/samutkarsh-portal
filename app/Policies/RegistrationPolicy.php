<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use App\Policies\Concerns\ChecksCenterOwnership;

class RegistrationPolicy
{
    use ChecksCenterOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_registration');
    }

    public function view(User $user, Registration $registration): bool
    {
        return $user->can('view_registration') && $this->ownsCenter($user, $registration->center_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create_registration');
    }

    public function update(User $user, Registration $registration): bool
    {
        return $user->can('update_registration') && $this->ownsCenter($user, $registration->center_id);
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $user->can('delete_registration') && $this->ownsCenter($user, $registration->center_id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_registration');
    }
}
