<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title' => $this->title,
            'start_at' => $this->start_at,
            'close_at' => $this->close_at,
            'place' => $this->place,
            'address' => $this->address,
            'additionnal_info' => $this->additionnal_info,

            // Frontend-specific fields
            'date' => $this->start_at,
            'time' => $this->start_at->format('H:i'),
            'location' => $this->place,

            'author' => new UserResource($this->whenLoaded('author')),
            // Assuming you have a TagResource
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
