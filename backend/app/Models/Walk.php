<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Walk extends Model
{
    /** @use HasFactory<\Database\Factories\WalkFactory> */
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'lat',
        'lng',
        'scheduled_at',
        'max_pets',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'scheduled_at' => 'datetime',
            'max_pets' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function pets(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'walk_pet', 'walk_id', 'pet_id')
            ->withTimestamps();
    }
}
