<?php

namespace App\Notifications;

use App\Models\HealthRecord;
use App\Services\RecommendationService;
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

        $mail = (new MailMessage)
            ->subject("Recordatorio de salud para {$petName}")
            ->greeting("¡Hola!")
            ->line("Tu mascota **{$petName}** tiene una próxima **{$typeLabel}** que vence en {$daysUntilDue} día(s).")
            ->line("Fecha programada: **{$this->healthRecord->next_due_date->format('d/m/Y')}**");

        // Para desparasitación: añadir combo de pastillas según peso
        if ($this->healthRecord->type === 'deworming') {
            $mail = $this->appendDewormingProducts($mail, $petName);
        }

        return $mail
            ->action('Ver historial sanitario', url('/pets/' . $this->healthRecord->pet->id . '/health'))
            ->line('Mantén actualizado el historial sanitario de tu mascota.');
    }

    private function appendDewormingProducts(MailMessage $mail, string $petName): MailMessage
    {
        $pet = $this->healthRecord->pet;
        if (!$pet || !$pet->weight) {
            return $mail;
        }

        $service = app(RecommendationService::class);
        $result = $service->getDewormingCombination($pet);

        if (empty($result['items'])) {
            return $mail;
        }

        $mail->line("---");
        $mail->line("🛍️ **Productos recomendados para la desparasitación de {$petName} ({$pet->weight} kg):**");

        foreach ($result['items'] as $item) {
            $product = $item['product'];
            $qty = $item['quantity'];
            $mail->line("• {$qty}× **{$product->name}** (dosis {$product->dosage_kg} kg) — {$product->price}€");
        }

        if ($result['covered']) {
            $mail->line("✅ Esta combinación cubre los {$pet->weight} kg de {$petName}.");
        }

        return $mail;
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

        $data = [
            'health_record_id' => $this->healthRecord->id,
            'pet_id' => $this->healthRecord->pet->id,
            'pet_name' => $petName,
            'type' => $this->healthRecord->type,
            'type_label' => $typeLabel,
            'next_due_date' => $this->healthRecord->next_due_date?->format('Y-m-d'),
            'days_until_due' => $daysUntilDue,
            'message' => "Próxima {$typeLabel} de {$petName}: {$this->healthRecord->next_due_date->format('d/m/Y')}",
            'product_recommendations' => null,
        ];

        // Para desparasitación: incluir combo de pastillas directamente en la notificación
        if ($this->healthRecord->type === 'deworming') {
            $pet = $this->healthRecord->pet;
            if ($pet && $pet->weight) {
                $service = app(RecommendationService::class);
                $result = $service->getDewormingCombination($pet);

                if (!empty($result['items'])) {
                    $data['product_recommendations'] = [
                        'type' => 'deworming_combo',
                        'pet_weight_kg' => $result['pet_weight_kg'],
                        'total_dosage_kg' => $result['total_dosage_kg'],
                        'covered' => $result['covered'],
                        'items' => collect($result['items'])->map(fn ($item) => [
                            'product_id' => $item['product']->id,
                            'product_name' => $item['product']->name,
                            'dosage_kg' => $item['product']->dosage_kg,
                            'price' => $item['product']->price,
                            'quantity' => $item['quantity'],
                        ])->values()->all(),
                    ];
                }
            }
        }

        return $data;
    }
}

