<?php

namespace App\Notifications;

use App\Models\PetFriendlyPlace;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlaceStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private PetFriendlyPlace $place,
        private string $status,
        private ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isApproved = $this->status === 'approved';

        $mail = new MailMessage();
        $mail->subject($isApproved ? '✅ Tu lugar ha sido aprobado' : '❌ Tu lugar ha sido rechazado');

        if ($isApproved) {
            $mail->greeting("¡Hola {$notifiable->name}!")
                ->line("Tu sugerencia de lugar \"{$this->place->name}\" ha sido **aprobada** y ahora es visible para todos los usuarios.")
                ->line("Categoría: **{$this->place->category}**")
                ->action('Ver el lugar', url("/places/{$this->place->id}"))
                ->line('¡Gracias por contribuir a la comunidad de PawConnect!');
        } else {
            $mail->greeting("Hola {$notifiable->name},")
                ->line("Lamentablemente, tu sugerencia de lugar \"{$this->place->name}\" ha sido **rechazada**.");

            if ($this->reason) {
                $mail->line("**Motivo:** {$this->reason}");
            }

            $mail->line('Si tienes preguntas, no dudes en contactarnos.')
                ->line('¡Esperamos tu próxima sugerencia!');
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'place_id' => $this->place->id,
            'place_name' => $this->place->name,
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => $this->status === 'approved'
                ? "Tu lugar '{$this->place->name}' ha sido aprobado"
                : "Tu lugar '{$this->place->name}' ha sido rechazado",
        ];
    }
}
