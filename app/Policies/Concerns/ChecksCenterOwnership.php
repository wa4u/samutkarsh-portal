<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksCenterOwnership
{
    /**
     * A Center Head may only act on records belonging to their own center.
     * Global roles (Trust Admin / Education Council) are never constrained here.
     * Trust Admin never reaches this code anyway — Gate::before short-circuits.
     */
    protected function ownsCenter(User $user, ?int $centerId): bool
    {
        if (! $user->isCenterHead()) {
            return true;
        }

        return $centerId !== null && $centerId === $user->center_id;
    }
}
