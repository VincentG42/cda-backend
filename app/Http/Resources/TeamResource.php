<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'name' => $this->name,
            'coach_id' => $this->coach_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'coach' => new UserResource($this->whenLoaded('coach')),
            'season' => new SeasonResource($this->whenLoaded('season')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'users_count' => $this->users_count,
        ];
    }
}
