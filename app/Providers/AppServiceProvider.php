<?php

namespace App\Providers;

use App\Repositories\CategoryRepository;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\EncounterRepository;
use App\Repositories\EncounterRepositoryInterface;
use App\Repositories\EventRepository;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\SeasonRepository;
use App\Repositories\SeasonRepositoryInterface;
use App\Repositories\TeamRepository;
use App\Repositories\TeamRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EncounterRepositoryInterface::class, EncounterRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SeasonRepositoryInterface::class, SeasonRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
