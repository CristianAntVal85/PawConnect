<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    /** @use HasFactory<\Database\Factories\PetFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'species',
        'name',
        'breed',
        'age',
        'weight',
        'size',
        'energy_level',
        'personality',
        'available_for_walks',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'weight' => 'decimal:2',
            'available_for_walks' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    // Alias requerido por el route model binding anidado de `pets.health`.
    public function healths(): HasMany
    {
        return $this->healthRecords();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PetPhoto::class)->orderBy('display_order');
    }

    public function walks(): BelongsToMany
    {
        return $this->belongsToMany(Walk::class, 'walk_pet', 'pet_id', 'walk_id')
            ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function matchesAsPetA(): HasMany
    {
        return $this->hasMany(PetMatch::class, 'pet_a_id');
    }

    public function matchesAsPetB(): HasMany
    {
        return $this->hasMany(PetMatch::class, 'pet_b_id');
    }
}
