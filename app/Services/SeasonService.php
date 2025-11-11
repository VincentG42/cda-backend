<?php

namespace App\Services;

use App\DTOs\CreateSeasonDTO;
use App\DTOs\UpdateSeasonDTO;
use App\Models\Season;
use App\Repositories\SeasonRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SeasonService
{
    public function __construct(
        private SeasonRepositoryInterface $seasonRepository
    ) {}

    public function getAllSeasons(): Collection
    {
        return $this->seasonRepository->all();
    }

    public function getSeasonById(int $id): ?Season
    {
        return $this->seasonRepository->find($id);
    }

    public function createSeason(CreateSeasonDTO $dto): Season
    {
        return $this->seasonRepository->create($dto);
    }

    public function updateSeason(Season $season, UpdateSeasonDTO $dto): bool
    {
        $data = $dto->toArray();

        if (isset($data['is_active']) && $data['is_active'] === true) {
            // Deactivate all other seasons
            Season::where('id', '!=', $season->id)->update(['is_active' => false]);
        }

        return $this->seasonRepository->update($season, $dto);
    }

    public function deleteSeason(Season $season): bool
    {
        return $this->seasonRepository->delete($season);
    }
}
