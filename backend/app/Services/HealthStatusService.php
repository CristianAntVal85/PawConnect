<?php

namespace App\Services;

use App\Models\HealthRecord;
use Carbon\Carbon;

class HealthStatusService
{
    /**
     * Estados posibles del registro sanitario
     */
    public const STATUS_GREEN = 'green';
    public const STATUS_YELLOW = 'yellow';
    public const STATUS_RED = 'red';

    /**
     * Días de anticipación para cambiar de verde a amarillo
     */
    private const WARNING_DAYS = 7;

    /**
     * Calcula el estado de un registro sanitario
     *
     * @param  HealthRecord  $record
     * @return array{status: string, days_remaining: int|null, is_overdue: bool, message: string}
     */
    public function getStatus(HealthRecord $record): array
    {
        if (is_null($record->next_due_date)) {
            return [
                'status' => self::STATUS_YELLOW,
                'days_remaining' => null,
                'is_overdue' => false,
                'message' => 'Registro sin fecha de próxima revisión',
            ];
        }

        $nextDueDate = Carbon::parse($record->next_due_date);
        $today = Carbon::today();
        $daysRemaining = $today->diffInDays($nextDueDate, false);

        if ($daysRemaining < 0) {
            // Vencido
            return [
                'status' => self::STATUS_RED,
                'days_remaining' => $daysRemaining,
                'is_overdue' => true,
                'message' => '⚠️ Revisión vencida hace ' . abs($daysRemaining) . ' días',
            ];
        }

        if ($daysRemaining <= self::WARNING_DAYS) {
            // Próximo a vencer
            return [
                'status' => self::STATUS_YELLOW,
                'days_remaining' => $daysRemaining,
                'is_overdue' => false,
                'message' => '⏰ Próximo a vencer en ' . $daysRemaining . ' días',
            ];
        }

        // Al día
        return [
            'status' => self::STATUS_GREEN,
            'days_remaining' => $daysRemaining,
            'is_overdue' => false,
            'message' => '✓ Al día, próxima revisión en ' . $daysRemaining . ' días',
        ];
    }

    /**
     * Obtiene todos los registros con estado para una mascota
     *
     * @param  \App\Models\Pet  $pet
     * @return array
     */
    public function getPetHealthWithStatus($pet): array
    {
        return $pet->healthRecords()
            ->latest()
            ->get()
            ->map(function ($record) {
                return array_merge(
                    $record->toArray(),
                    ['health_status' => $this->getStatus($record)]
                );
            })
            ->toArray();
    }

    /**
     * Obtiene el estado general de una mascota (peor estado de todos sus registros)
     *
     * @param  \App\Models\Pet  $pet
     * @return string
     */
    public function getPetGeneralStatus($pet): string
    {
        $records = $pet->healthRecords()->get();

        if ($records->isEmpty()) {
            return self::STATUS_YELLOW;
        }

        foreach ($records as $record) {
            $status = $this->getStatus($record);
            if ($status['status'] === self::STATUS_RED) {
                return self::STATUS_RED;
            }
        }

        foreach ($records as $record) {
            $status = $this->getStatus($record);
            if ($status['status'] === self::STATUS_YELLOW) {
                return self::STATUS_YELLOW;
            }
        }

        return self::STATUS_GREEN;
    }
}
