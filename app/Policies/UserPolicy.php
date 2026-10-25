<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function updateRole(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }
}
