<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('access-admin-panel');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // Admin-level users can view any team.
        if ($user->can('access-admin-panel')) {
            return true;
        }

        // A coach can view their own team.
        if (strtolower($user->userType->name) === 'coach') {
            return $user->id === $team->coach_id;
        }

        // A player can view a team they are a member of.
        if (strtolower($user->userType->name) === 'player') {
            return $user->teams()->where('team_id', $team->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('access-admin-panel');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->can('access-admin-panel');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->can('access-admin-panel');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Team $team): bool
    {
        return $user->can('access-admin-panel');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return $user->can('access-admin-panel');
    }
}
