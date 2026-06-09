<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

/**
 * Settings: Trust Admin (full, via Gate::before) and Education Council
 * (view + update curriculum/site settings, but not create/delete).
 */
class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_setting');
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->can('view_setting');
    }

    public function create(User $user): bool
    {
        return $user->can('create_setting');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->can('update_setting');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->can('delete_setting');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_setting');
    }
}
