<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;
use App\Models\UserType;

class EncounterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view encounters
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Encounter $encounter): bool
    {
        return true; // Any authenticated user can view a specific encounter
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Encounter $encounter): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Encounter $encounter): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Encounter $encounter): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Encounter $encounter): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }
}
