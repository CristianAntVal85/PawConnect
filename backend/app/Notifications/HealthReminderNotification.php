<?php

namespace App\Notifications;

use App\Models\HealthRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HealthReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected HealthRecord $healthRecord;

    /**
     * Create a new notification instance.
     */
    public function __construct(HealthRecord $healthRecord)
    {
        $this->healthRecord = $healthRecord;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $petName = $this->healthRecord->pet->name ?? 'Pet';
        $daysUntilDue = $this->healthRecord->next_due_date?->diffInDays(now()) ?? 0;
        
        $typeLabel = match ($this->healthRecord->type) {
            'vaccination' => 'Vacunación',
            'deworming' => 'Desparasitación',
            'checkup' => 'Revisión',
            'surgery' => 'Cirugía',
            'medication' => 'Medicación',
            default => 'Revisión sanitaria',
        };

        return (new MailMessage)
            ->subject("Recordatorio de salud para {$petName}")
            ->greeting("¡Hola!")
            ->line("Tu mascota **{$petName}** tiene una próxima **{$typeLabel}** que vence en {$daysUntilDue} día(s).")
            ->line("Fecha programada: **{$this->healthRecord->next_due_date->format('d/m/Y')}**")
            ->action('Ver historial sanitario', url('/pets/' . $this->healthRecord->pet->id . '/health'))
            ->line('Mantén actualizado el historial sanitario de tu mascota.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $petName = $this->healthRecord->pet->name ?? 'Pet';
        $daysUntilDue = (int) now()->diffInDays($this->healthRecord->next_due_date, false) ?? 0;
        
        $typeLabel = match ($this->healthRecord->type) {
            'vaccination' => 'Vacunación',
            'deworming' => 'Desparasitación',
            'checkup' => 'Revisión',
            'surgery' => 'Cirugía',
            'medication' => 'Medicación',
            default => 'Revisión sanitaria',
        };

        return [
            'health_record_id' => $this->healthRecord->id,
            'pet_id' => $this->healthRecord->pet->id,
            'pet_name' => $petName,
            'type' => $this->healthRecord->type,
            'type_label' => $typeLabel,
            'next_due_date' => $this->healthRecord->next_due_date?->format('Y-m-d'),
            'days_until_due' => $daysUntilDue,
            'message' => "Próxima {$typeLabel} de {$petName}: {$this->healthRecord->next_due_date->format('d/m/Y')}",
        ];
    }
}

