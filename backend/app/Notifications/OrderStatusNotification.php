<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
        public ?string $note = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->getStatusLabel($this->newStatus);
        $statusIcon  = $this->getStatusIcon($this->newStatus);

        return (new MailMessage)
            ->subject("Pedido #{$this->order->id}: {$statusLabel} {$statusIcon}")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Tu pedido #{$this->order->id} ha cambiado de estado:")
            ->line("**{$this->getStatusLabel($this->oldStatus)} → {$statusLabel}**")
            ->when($this->note, fn ($mail) => $mail->line("Nota: {$this->note}"))
            ->line("Producto: {$this->order->product->name}")
            ->line("Cantidad: {$this->order->quantity} ud.")
            ->line("Total: ${$this->order->total_price}")
            ->action('Ver pedido', url("/orders/{$this->order->id}"))
            ->line('Gracias por comprar en PawConnect.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'order_status_changed',
            'order_id'      => $this->order->id,
            'product_name'  => $this->order->product->name,
            'old_status'    => $this->oldStatus,
            'new_status'    => $this->newStatus,
            'note'          => $this->note,
            'icon'          => $this->getStatusIcon($this->newStatus),
            'message'       => "Tu pedido #{$this->order->id} está {$this->getStatusLabel($this->newStatus)}",
            'url'           => "/orders/{$this->order->id}",
        ];
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending'    => 'Pendiente',
            'confirmed'  => 'Confirmado',
            'shipped'    => 'Enviado',
            'delivered'  => 'Entregado',
            'cancelled'  => 'Cancelado',
            default      => ucfirst($status),
        };
    }

    private function getStatusIcon(string $status): string
    {
        return match ($status) {
            'pending'    => '⏳',
            'confirmed'  => '✅',
            'shipped'    => '🚚',
            'delivered'  => '📦',
            'cancelled'  => '❌',
            default      => '•',
        };
    }
}
