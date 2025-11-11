<?php

namespace App\Repositories;

use App\DTOs\CreateSeasonDTO;
use App\DTOs\UpdateSeasonDTO;
use App\Models\Season;
use Illuminate\Database\Eloquent\Collection;

class SeasonRepository implements SeasonRepositoryInterface
{
    public function all(): Collection
    {
        return Season::all();
    }

    public function find(int $id): ?Season
    {
        return Season::find($id);
    }

    public function create(CreateSeasonDTO $dto): Season
    {
        return Season::create($dto->toArray());
    }

    public function update(Season $season, UpdateSeasonDTO $dto): bool
    {
        return $season->update($dto->toArray());
    }

    public function delete(Season $season): bool
    {
        return $season->delete();
    }
}
