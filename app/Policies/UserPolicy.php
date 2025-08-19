<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->userType->name, [UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return in_array($user->userType->name, [UserType::PRESIDENT, UserType::STAFF, UserType::COACH]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->userType->name, [UserType::PRESIDENT, UserType::STAFF]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->userType->name === UserType::PRESIDENT) {
            return true;
        }

        if ($user->userType->name === UserType::STAFF && in_array($model->userType->name, [UserType::COACH, UserType::PLAYER])) {
            return true;
        }

        if ($user->userType->name === UserType::COACH && $model->userType->name === UserType::PLAYER) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->userType->name === UserType::PRESIDENT) {
            return true;
        }

        if ($user->userType->name === UserType::STAFF && in_array($model->userType->name, [UserType::COACH, UserType::PLAYER])) {
            return true;
        }

        if ($user->userType->name === UserType::COACH && $model->userType->name === UserType::PLAYER) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return in_array($user->userType->name, [UserType::PRESIDENT, UserType::STAFF]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->userType->name === UserType::PRESIDENT;
    }
}
