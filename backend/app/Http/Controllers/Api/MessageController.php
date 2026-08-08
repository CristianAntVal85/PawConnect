<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\PetMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PetMatch $match, Request $request): JsonResponse
    {
        abort_unless(
            $this->userBelongsToMatch($match, (int) $request->user()->id),
            403,
            'No autorizado para acceder a este chat.'
        );

        $messages = $match->messages()
            ->with('sender:id,name')
            ->oldest('created_at')
            ->get();

        return response()->json([
            'message' => 'Mensajes obtenidos correctamente.',
            'data' => MessageResource::collection($messages),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PetMatch $match): JsonResponse
    {
        abort_unless(
            $this->userBelongsToMatch($match, (int) $request->user()->id),
            403,
            'No autorizado para enviar mensajes en este chat.'
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $match->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $message->loadMissing('sender:id,name');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Mensaje enviado correctamente.',
            'data' => new MessageResource($message),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function userBelongsToMatch(PetMatch $match, int $userId): bool
    {
        $match->loadMissing([
            'petA:id,user_id',
            'petB:id,user_id',
        ]);

        return (int) $match->petA?->user_id === $userId
            || (int) $match->petB?->user_id === $userId;
    }
}
