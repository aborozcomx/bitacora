<?php

namespace App\Policies;

use App\Models\Bitacora;
use App\Models\User;

class BitacoraPolicy
{
    /**
     * Determine whether the user can view any bitacoras.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific bitacora.
     */
    public function view(User $user, Bitacora $bitacora): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->branches->contains($bitacora->branch_id) || $bitacora->user_id === $user->id;
    }

    /**
     * Determine whether the user can create bitacoras (Any user).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the bitacora.
     */
    public function update(User $user, Bitacora $bitacora): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->branches->contains($bitacora->branch_id) || $bitacora->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the bitacora.
     */
    public function delete(User $user, Bitacora $bitacora): bool
    {
        return $user->hasRole('admin');
    }
}
