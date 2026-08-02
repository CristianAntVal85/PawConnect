<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    /**
     * Perfil del usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Perfil obtenido correctamente.',
            'data' => $request->user(),
        ]);
    }

    /**
     * Actualizar perfil del usuario autenticado.
     */
    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'photo' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Eliminar cuenta propia.
     */
    public function destroyMe(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Cuenta eliminada correctamente.',
        ]);
    }

    /**
     * Listar usuarios (solo admin por ruta).
     */
    public function index(): JsonResponse
    {
        $users = User::query()->latest()->paginate(15);

        return response()->json([
            'message' => 'Usuarios obtenidos correctamente.',
            'data' => $users,
        ]);
    }

    /**
     * Crear usuario (solo admin por ruta).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'role' => ['nullable', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'photo' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:255'],
        ]);

        if (! isset($validated['role'])) {
            $validated['role'] = User::ROLE_USER;
        }

        $user = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data' => $user,
        ], 201);
    }

    /**
     * Ver usuario por ID (solo admin por ruta).
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'Usuario obtenido correctamente.',
            'data' => $user,
        ]);
    }

    /**
     * Actualizar usuario por ID (solo admin por ruta).
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'role' => ['sometimes', 'required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'photo' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Eliminar usuario por ID (solo admin por ruta).
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }
}
