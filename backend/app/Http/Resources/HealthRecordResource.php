<?php

namespace App\Http\Resources;

use App\Services\HealthStatusService;
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
        $statusService = new HealthStatusService();
        $status = $statusService->getStatus($this->resource);

        return [
            'id' => $this->id,
            'pet_id' => $this->pet_id,
            'type' => $this->type,
            'last_date' => $this->last_date?->toDateString(),
            'periodicity_days' => $this->periodicity_days,
            'notes' => $this->notes,
            'notifications_active' => (bool) $this->notifications_active,
            'next_due_date' => $this->next_due_date?->toDateString(),
            'health_status' => [
                'status' => $status['status'],
                'days_remaining' => $status['days_remaining'],
                'is_overdue' => $status['is_overdue'],
                'message' => $status['message'],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
