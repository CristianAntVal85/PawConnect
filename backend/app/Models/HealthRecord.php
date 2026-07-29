<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    /** @use HasFactory<\Database\Factories\HealthRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'type',
        'last_date',
        'periodicity_days',
        'notes',
        'notifications_active',
        'next_due_date',
    ];

    protected function casts(): array
    {
        return [
            'last_date' => 'date',
            'periodicity_days' => 'integer',
            'notifications_active' => 'boolean',
            'next_due_date' => 'date',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
}
