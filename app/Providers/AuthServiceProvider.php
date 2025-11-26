<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\Encounter' => 'App\Policies\EncounterPolicy',
        'App\Models\Event' => 'App\Policies\EventPolicy',
        'App\Models\Team' => 'App\Policies\TeamPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url', 'http://localhost:4321') . "/reset-password?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::before(function ($user, $ability) {
            if ($user->userType->name === UserType::ADMIN) {
                return true;
            }
        });

        Gate::define('access-admin-panel', function (User $user) {
            return in_array($user->userType->name, [
                UserType::ADMIN,
                UserType::PRESIDENT,
                UserType::STAFF,
                UserType::COACH,
            ]);
        });
    }
}
