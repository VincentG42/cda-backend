<?php

namespace App\Repositories;

use App\DTOs\CreateTeamDTO;
use App\DTOs\UpdateTeamDTO;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TeamRepositoryInterface
{
    public function getAll(array $relations = []): LengthAwarePaginator;

    public function create(CreateTeamDTO $dto): Team;

    public function update(Team $team, UpdateTeamDTO $dto): bool;

    public function delete(Team $team): bool;

    public function addPlayer(Team $team, User $user): void;

    public function removePlayer(Team $team, User $player): int;

    public function assignCoach(Team $team, User $coach): bool;

    public function isPlayerInTeam(Team $team, User $user): bool;
}
