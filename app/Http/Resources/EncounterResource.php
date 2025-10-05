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
        return [
            'id' => $this->id,
            'opponent' => $this->opponent,
            'is_at_home' => (bool) $this->is_at_home,
            'happens_at' => $this->happens_at,
            'time' => $this->happens_at->format('H:i'),
            'location' => $this->location,
            'is_victory' => $this->is_victory === null ? null : (bool) $this->is_victory,
            'season' => new SeasonResource($this->whenLoaded('season')),
            'team' => new TeamResource($this->whenLoaded('team')),
        ];
    }
}
