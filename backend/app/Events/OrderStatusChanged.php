<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
        public ?string $note = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("order.{$this->order->id}"),
            new PrivateChannel("user.{$this->order->user_id}.orders"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status-changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'  => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'note'      => $this->note,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
