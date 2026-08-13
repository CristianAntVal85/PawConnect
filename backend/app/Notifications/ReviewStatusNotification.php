<?php

namespace App\Notifications;

use App\Models\PlaceReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlaceReview $review,
        public string $status,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = new MailMessage();
        $message->subject("Tu reseña ha sido {$this->statusLabel()}");
        $message->greeting("Hola {$notifiable->name},");

        if ($this->status === 'approved') {
            $message->line("Tu reseña sobre **{$this->review->place->name}** ha sido **aprobada**.")
                ->line("Ahora es visible para otros usuarios.")
                ->action('Ver lugar', route('places.show', $this->review->place->id));
        } else {
            $message->line("Tu reseña sobre **{$this->review->place->name}** ha sido **rechazada**.");
            if ($this->reason) {
                $message->line("Motivo: {$this->reason}");
            }
        }

        return $message
            ->line('Gracias por ayudarnos a mantener PawConnect seguro.')
            ->salutation('Saludos, PawConnect');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'place_id' => $this->review->place_id,
            'place_name' => $this->review->place->name,
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => "Tu reseña ha sido {$this->statusLabel()}.",
        ];
    }

    private function statusLabel(): string
    {
        return $this->status === 'approved' ? 'aprobada' : 'rechazada';
    }
}
