<?php

use App\Models\PetMatch;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('match.{matchId}', function ($user, int $matchId) {
    return PetMatch::query()
        ->whereKey($matchId)
        ->where(function ($query) use ($user) {
            $query->whereHas('petA', function ($petQuery) use ($user) {
                $petQuery->where('user_id', $user->id);
            })->orWhereHas('petB', function ($petQuery) use ($user) {
                $petQuery->where('user_id', $user->id);
            });
        })
        ->exists();
}, ['guards' => ['sanctum']]);
