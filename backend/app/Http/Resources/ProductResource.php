<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'provider_id' => $this->provider_id,
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'min_weight_kg' => $this->min_weight_kg ? (float) $this->min_weight_kg : null,
            'max_weight_kg' => $this->max_weight_kg ? (float) $this->max_weight_kg : null,
            'recommended_breeds' => $this->recommended_breeds ?? [],
            'photo' => $this->photo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
