<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;

class PetPolicy
{
    /**
     * Determina si el usuario puede ver cualquier mascota.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver la mascota.
     */
    public function view(User $user, Pet $pet): bool
    {
        return $user->isAdmin() || $pet->user_id === $user->id;
    }

    /**
     * Determina si el usuario puede crear mascotas.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede actualizar la mascota.
     */
    public function update(User $user, Pet $pet): bool
    {
        return $user->isAdmin() || $pet->user_id === $user->id;
    }

    /**
     * Determina si el usuario puede eliminar la mascota.
     */
    public function delete(User $user, Pet $pet): bool
    {
        return $user->isAdmin() || $pet->user_id === $user->id;
    }
}
