<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\ChecksCenterOwnership;

class StudentPolicy
{
    use ChecksCenterOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_student');
    }

    public function view(User $user, Student $student): bool
    {
        return $user->can('view_student') && $this->ownsCenter($user, $student->center_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create_student');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->can('update_student') && $this->ownsCenter($user, $student->center_id);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can('delete_student') && $this->ownsCenter($user, $student->center_id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_student');
    }
}
