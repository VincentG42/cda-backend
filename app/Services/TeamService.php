<?php

namespace App\Services;

use App\DTOs\CreateTeamDTO;
use App\DTOs\UpdateTeamDTO;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use App\Repositories\TeamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TeamService
{
    public function __construct(
        private TeamRepositoryInterface $teamRepository
    ) {}

    public function getAllTeams(): LengthAwarePaginator
    {
        return $this->teamRepository->getAll(['category', 'coach', 'season']);
    }

    public function createTeam(CreateTeamDTO $dto): Team
    {
        return $this->teamRepository->create($dto);
    }

    public function updateTeam(Team $team, UpdateTeamDTO $dto): bool
    {
        return $this->teamRepository->update($team, $dto);
    }

    public function deleteTeam(Team $team): bool
    {
        return $this->teamRepository->delete($team);
    }

    public function addPlayer(Team $team, User $user): Team
    {
        return DB::transaction(function () use ($team, $user) {
            if ($this->teamRepository->isPlayerInTeam($team, $user)) {
                throw new InvalidArgumentException('Player is already in the team.', 409);
            }

            $this->teamRepository->addPlayer($team, $user);

            return $team->load('users');
        });
    }

    public function removePlayer(Team $team, User $player): void
    {
        DB::transaction(function () use ($team, $player) {
            if (! $this->teamRepository->isPlayerInTeam($team, $player)) {
                throw new InvalidArgumentException('Player not found in team.', 404);
            }

            $this->teamRepository->removePlayer($team, $player);
        });
    }

    public function assignCoach(Team $team, User $coach): Team
    {
        return DB::transaction(function () use ($team, $coach) {
            if ($coach->userType->name !== UserType::COACH) {
                throw new InvalidArgumentException('User is not a coach.', 422);
            }

            if ($team->coach_id === $coach->id) {
                throw new InvalidArgumentException('Coach is already assigned to this team.', 409);
            }

            $this->teamRepository->assignCoach($team, $coach);

            return $team->load('coach');
        });
    }
}
