<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetMatch extends Model
{
    /** @use HasFactory<\Database\Factories\PetMatchFactory> */
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'pet_a_id',
        'pet_b_id',
        'status',
        'matched_at',
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
        ];
    }

    public function petA(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_a_id');
    }

    public function petB(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_b_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'match_id');
    }
}
