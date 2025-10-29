<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Zone;

class ZonePolicy
{
    public function isAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Zone $zone): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Zone $zone): bool
    {
        return $user->isAdmin();
    }
}
