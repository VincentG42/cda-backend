<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'licence_number' => $this->licence_number,
            'has_to_change_password' => $this->has_to_change_password,
            'user_type' => [
                'id' => $this->user_type_id,
                'name' => $this->userType->name,
            ],
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'individual_stats' => IndividualStatResource::collection($this->whenLoaded('individualStats')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
