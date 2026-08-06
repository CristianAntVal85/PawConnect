<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthRecordResource extends JsonResource
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
            'pet_id' => $this->pet_id,
            'type' => $this->type,
            'last_date' => $this->last_date?->toDateString(),
            'periodicity_days' => $this->periodicity_days,
            'notes' => $this->notes,
            'notifications_active' => (bool) $this->notifications_active,
            'next_due_date' => $this->next_due_date?->toDateString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
