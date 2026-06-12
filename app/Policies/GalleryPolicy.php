<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;
use App\Policies\Concerns\ChecksCenterOwnership;

class GalleryPolicy
{
    use ChecksCenterOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_gallery');
    }

    public function view(User $user, Gallery $gallery): bool
    {
        return $user->can('view_gallery') && $this->ownsCenter($user, $gallery->center_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create_gallery');
    }

    public function update(User $user, Gallery $gallery): bool
    {
        return $user->can('update_gallery') && $this->ownsCenter($user, $gallery->center_id);
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        return $user->can('delete_gallery') && $this->ownsCenter($user, $gallery->center_id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_gallery');
    }

    /** Moderation — Trust Admin only (granted via Gate::before). */
    public function approve(User $user, Gallery $gallery): bool
    {
        return $user->can('approve_gallery');
    }
}
