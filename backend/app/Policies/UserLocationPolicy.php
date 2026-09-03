<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserLocation;

class UserLocationPolicy
{
    public function update(User $user, UserLocation $location): bool
    {
        return $location->user_id === $user->id;
    }

    public function delete(User $user, UserLocation $location): bool
    {
        return $location->user_id === $user->id;
    }
}
