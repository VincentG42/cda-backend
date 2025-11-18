<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncounterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $homeTeamName = $this->is_at_home ? $this->team->name : $this->opponent;
        $awayTeamName = $this->is_at_home ? $this->opponent : $this->team->name;

        return [
            'id' => $this->id,
            'home_team' => [
                'name' => $homeTeamName,
                'id' => $this->is_at_home ? $this->team->id : null,
            ],
            'away_team' => [
                'name' => $awayTeamName,
                'id' => $this->is_at_home ? null : $this->team->id,
            ],
            'opponent' => $this->opponent,
            'is_at_home' => (bool) $this->is_at_home,
            'happens_at' => $this->happens_at,
            'time' => $this->happens_at->format('H:i'),
            'location' => $this->location,
            'team_score' => $this->team_score,
            'opponent_score' => $this->opponent_score,
            'is_victory' => $this->is_victory === null ? null : (bool) $this->is_victory,
            'season' => new SeasonResource($this->whenLoaded('season')),
            'season_id' => $this->season_id,
            'team' => new TeamResource($this->whenLoaded('team')),
            'team_id' => $this->team_id,
        ];
    }
}
