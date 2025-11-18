<?php

namespace App\Repositories;

use App\DTOs\CreateEncounterDTO;
use App\DTOs\EncounterFilterDTO;
use App\DTOs\UpdateEncounterDTO;
use App\Models\Encounter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EncounterRepository implements EncounterRepositoryInterface
{
    public function getFiltered(EncounterFilterDTO $dto, array $relations = []): LengthAwarePaginator
    {
        $query = Encounter::with($relations);

        $query->when($dto->team_id, function ($q) use ($dto) {
            $q->where('team_id', $dto->team_id);
        });

        if ($dto->filter === 'past') {
            $query->where('happens_at', '<', now());
        } elseif ($dto->filter === 'upcoming') {
            $query->where('happens_at', '>=', now());
        }

        return $query->orderByDesc('happens_at')->paginate(15);
    }

    public function create(CreateEncounterDTO $dto): Encounter
    {
        return Encounter::create($dto->toArray());
    }

    public function update(Encounter $encounter, UpdateEncounterDTO $dto): bool
    {
        return $encounter->update($dto->toArray());
    }

    public function delete(Encounter $encounter): bool
    {
        return $encounter->delete();
    }
}
