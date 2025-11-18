<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndividualStatResource extends JsonResource
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
            'encounter_id' => $this->encounter_id,
            'user_id' => $this->user_id,
            'json_data' => json_decode($this->json), // Decode the JSON string
            'encounter' => new EncounterResource($this->whenLoaded('encounter')), // Load related encounter
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
