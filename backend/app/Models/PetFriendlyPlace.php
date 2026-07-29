<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetFriendlyPlace extends Model
{
    /** @use HasFactory<\Database\Factories\PetFriendlyPlaceFactory> */
    use HasFactory;

    protected $table = 'petfriendly_places';

    protected $fillable = [
        'name',
        'category',
        'description',
        'lat',
        'lng',
        'address',
        'phone',
        'website',
        'photo',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PlaceReview::class, 'place_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'place_favorites', 'place_id', 'user_id')
            ->withTimestamps();
    }
}
