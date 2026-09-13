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
        if ($bitacora->is_closed) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $bitacora->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the bitacora.
     */
    public function delete(User $user, Bitacora $bitacora): bool
    {
        if ($bitacora->is_closed) {
            return false;
        }

        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can close the bitacora/folio.
     */
    public function close(User $user, Bitacora $bitacora): bool
    {
        if ($bitacora->is_closed) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $bitacora->user_id === $user->id;
    }
}
