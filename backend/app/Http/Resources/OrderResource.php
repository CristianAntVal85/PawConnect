<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'pet_id' => $this->pet_id,
            'quantity' => (int) $this->quantity,
            'total_price' => (float) $this->total_price,
            'shipping_address' => $this->shipping_address,
            'payment_method' => $this->payment_method,
            'stripe_payment_id' => $this->stripe_payment_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
