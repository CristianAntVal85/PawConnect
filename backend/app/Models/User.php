<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
        'lat',
        'lng',
        'address',
        'billing_address',
        'blocked_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'blocked_at' => 'datetime',
        ];
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function placeReviews(): HasMany
    {
        return $this->hasMany(PlaceReview::class);
    }

    public function createdPlaces(): HasMany
    {
        return $this->hasMany(PetFriendlyPlace::class, 'created_by');
    }

    public function favoritePlaces(): BelongsToMany
    {
        return $this->belongsToMany(PetFriendlyPlace::class, 'place_favorites', 'user_id', 'place_id')
            ->withTimestamps();
    }

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function walksCreated(): HasMany
    {
        return $this->hasMany(Walk::class, 'creator_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function block(): bool
    {
        return $this->update(['blocked_at' => now()]);
    }

    public function unblock(): bool
    {
        return $this->update(['blocked_at' => null]);
    }
}
