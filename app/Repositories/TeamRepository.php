<?php

namespace App\Repositories;

use App\DTOs\CreateTeamDTO;
use App\DTOs\UpdateTeamDTO;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamRepository implements TeamRepositoryInterface
{
    public function getAll(array $relations = []): LengthAwarePaginator
    {
        return Team::with($relations)->paginate(15);
    }

    public function create(CreateTeamDTO $dto): Team
    {
        return Team::create($dto->toArray());
    }

    public function update(Team $team, UpdateTeamDTO $dto): bool
    {
        return $team->update($dto->toArray());
    }

    public function delete(Team $team): bool
    {
        return $team->delete();
    }

    public function addPlayer(Team $team, User $user): void
    {
        $team->users()->attach($user->id);
    }

    public function removePlayer(Team $team, User $player): int
    {
        return $team->users()->detach($player->id);
    }

    public function assignCoach(Team $team, User $coach): bool
    {
        $team->coach_id = $coach->id;

        return $team->save();
    }

    public function isPlayerInTeam(Team $team, User $user): bool
    {
        return $team->users()->where('user_id', $user->id)->exists();
    }
}
