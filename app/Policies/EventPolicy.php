<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Models\UserType;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view events
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        return true; // Any authenticated user can view a specific event
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return in_array($user->userType->name, [UserType::ADMIN, UserType::PRESIDENT, UserType::STAFF]);
    }
}
