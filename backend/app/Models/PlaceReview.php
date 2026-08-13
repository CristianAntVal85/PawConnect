<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceReview extends Model
{
    /** @use HasFactory<\Database\Factories\PlaceReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id',
        'user_id',
        'rating',
        'body',
        'photo',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => 'string',
        ];
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(PetFriendlyPlace::class, 'place_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
