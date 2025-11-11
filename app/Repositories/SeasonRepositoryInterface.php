<?php

namespace App\Repositories;

use App\DTOs\CreateSeasonDTO;
use App\DTOs\UpdateSeasonDTO;
use App\Models\Season;
use Illuminate\Database\Eloquent\Collection;

interface SeasonRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Season;

    public function create(CreateSeasonDTO $dto): Season;

    public function update(Season $season, UpdateSeasonDTO $dto): bool;

    public function delete(Season $season): bool;
}
