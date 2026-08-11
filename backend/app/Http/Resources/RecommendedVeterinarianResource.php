<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendedVeterinarianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'place' => [
                'id' => $this->place->id,
                'name' => $this->place->name,
                'category' => $this->place->category,
                'description' => $this->place->description,
                'address' => $this->place->address,
                'lat' => $this->place->lat,
                'lng' => $this->place->lng,
                'phone' => $this->place->phone,
                'website' => $this->place->website,
                'photo' => $this->place->photo,
                'status' => $this->place->status,
            ],
            'distance' => round($this->distance, 2),
            'distance_unit' => 'km',
            'needed_services' => $this->needed_services,
            'urgency' => $this->urgency,
        ];
    }
}
