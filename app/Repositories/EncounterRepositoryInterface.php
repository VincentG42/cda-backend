<?php

namespace App\Repositories;

use App\DTOs\CreateEncounterDTO;
use App\DTOs\EncounterFilterDTO;
use App\DTOs\UpdateEncounterDTO;
use App\Models\Encounter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EncounterRepositoryInterface
{
    public function getFiltered(EncounterFilterDTO $dto, array $relations = []): LengthAwarePaginator;

    public function create(CreateEncounterDTO $dto): Encounter;

    public function update(Encounter $encounter, UpdateEncounterDTO $dto): bool;

    public function delete(Encounter $encounter): bool;
}
