<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    /** @use HasFactory<\Database\Factories\UserLocationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'lat',
        'lng',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'lat'        => 'decimal:7',
            'lng'        => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
