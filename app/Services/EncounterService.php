<?php

namespace App\Services;

use App\DTOs\CreateEncounterDTO;
use App\DTOs\EncounterFilterDTO;
use App\DTOs\UpdateEncounterDTO;
use App\Models\Encounter;
use App\Repositories\EncounterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EncounterService
{
    public function __construct(
        private EncounterRepositoryInterface $encounterRepository
    ) {}

    public function getFilteredEncounters(EncounterFilterDTO $dto): LengthAwarePaginator
    {
        return $this->encounterRepository->getFiltered($dto, ['season', 'team']);
    }

    public function createEncounter(CreateEncounterDTO $dto): Encounter
    {
        return $this->encounterRepository->create($dto);
    }

    public function updateEncounter(Encounter $encounter, UpdateEncounterDTO $dto): bool
    {
        return $this->encounterRepository->update($encounter, $dto);
    }

    public function deleteEncounter(Encounter $encounter): bool
    {
        return $this->encounterRepository->delete($encounter);
    }
}
