<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PetMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request, ?PetMatch $match = null): View
    {
        $user = $request->user();
        $petIds = $user->pets()->pluck('id');

        $matches = PetMatch::query()
            ->where(function ($query) use ($petIds) {
                $query->whereIn('pet_a_id', $petIds)
                    ->orWhereIn('pet_b_id', $petIds);
            })
            ->with([
                'petA.photos',
                'petA.user:id,name',
                'petB.photos',
                'petB.user:id,name',
                'latestMessage.sender:id,name',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            ->latest('updated_at')
            ->get();

        if ($match && ! $matches->contains('id', $match->id)) {
            abort(403, 'No autorizado para acceder a esta conversacion.');
        }

        $activeMatch = $match ?? $matches->first();

        $conversations = $matches->map(function (PetMatch $petMatch) use ($user) {
            $isOwnerOfPetA = (int) $petMatch->petA?->user_id === (int) $user->id;
            $counterpartPet = $isOwnerOfPetA ? $petMatch->petB : $petMatch->petA;
            $avatar = $counterpartPet?->photos?->first()?->path;

            return [
                'id' => $petMatch->id,
                'title' => $counterpartPet?->name ?? 'Mascota',
                'subtitle' => $counterpartPet?->user?->name ?? 'Usuario',
                'avatar' => $avatar ? asset('storage/'.$avatar) : null,
                'last_message' => $petMatch->latestMessage?->body,
                'last_message_at' => $petMatch->latestMessage?->created_at?->toISOString(),
                'unread_count' => (int) $petMatch->unread_count,
            ];
        })->values();

        return view('chat.index', [
            'conversations' => $conversations,
            'activeMatchId' => $activeMatch?->id,
            'currentUserId' => $user->id,
        ]);
    }
}
