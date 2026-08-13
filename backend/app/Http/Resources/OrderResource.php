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
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'product_id'        => $this->product_id,
            'pet_id'            => $this->pet_id,
            'quantity'          => (int) $this->quantity,
            'total_price'       => (float) $this->total_price,
            'shipping_address'  => $this->shipping_address,
            'payment_method'    => $this->payment_method,
            'stripe_payment_id' => $this->stripe_payment_id,
            'tracking_number'   => $this->tracking_number,
            'tracking_url'      => $this->tracking_url,
            'status'            => $this->status,
            'product'           => $this->whenLoaded('product', fn () => [
                'id'    => $this->product->id,
                'name'  => $this->product->name,
                'price' => (float) $this->product->price,
                'photo' => $this->product->photo,
            ]),
            'pet' => $this->whenLoaded('pet', fn () => [
                'id'   => $this->pet->id,
                'name' => $this->pet->name,
            ]),
            'status_history' => $this->whenLoaded('statusHistory', fn () =>
                $this->statusHistory->map(fn ($h) => [
                    'status'     => $h->status,
                    'note'       => $h->note,
                    'created_at' => $h->created_at,
                ])
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
