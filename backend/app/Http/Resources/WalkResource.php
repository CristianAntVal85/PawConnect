<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $participantCount = $this->whenCounted('pets');

        if ($participantCount instanceof \Illuminate\Http\Resources\MissingValue) {
            $participantCount = $this->relationLoaded('pets') ? $this->pets->count() : 0;
        }

        return [
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'title' => $this->title,
            'description' => $this->description,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'max_pets' => $this->max_pets,
            'participant_count' => $participantCount,
            'spots_left' => max(0, $this->max_pets - $participantCount),
            'distance_km' => isset($this->distance_km) ? (float) $this->distance_km : null,
            'participants' => $this->whenLoaded('pets', fn () => $this->pets
                ->map(fn ($pet) => [
                    'id' => $pet->id,
                    'user_id' => $pet->user_id,
                    'name' => $pet->name,
                    'species' => $pet->species,
                    'breed' => $pet->breed,
                    'available_for_walks' => (bool) $pet->available_for_walks,
                ])
                ->values()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
