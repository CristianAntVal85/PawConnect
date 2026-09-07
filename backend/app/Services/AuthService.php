<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthService - Lógica compartida de autenticación
 *
 * Usada por:
 * - Api/AuthController (devuelve tokens Sanctum)
 * - Web/LoginController (devuelve sesiones web)
 * - Web/RegisterController (devuelve sesiones web)
 * - Web/ForgotPasswordController (devuelve emails)
 */
class AuthService
{
    /**
     * Validar credenciales sin autenticar (usado por ambos controllers)
     *
     * @return User
     * @throws ValidationException
     */
    public function validateCredentials(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        return $user;
    }

    /**
     * Crear usuario desde datos de registro
     */
    public function createUser(array $validated): User
    {
        return User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => User::ROLE_USER,
        ]);
    }

    /**
     * Autenticar usuario con sesión web
     * (Solo para Web, no para API)
     *
     * Nota: Las credenciales ya fueron validadas en validateCredentials(),
     * por lo que Auth::login() es suficiente y más eficiente que Auth::attempt()
     */
    public function authenticateWeb(User $user, bool $remember = false): void
    {
        Auth::login($user, $remember);
    }

    /**
     * Revoke all existing tokens for a user
     * (Only for API logout)
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Revoke old tokens with same name (session unique per device)
     */
    public function revokeSameName(User $user, string $name = 'auth_token'): void
    {
        $user->tokens()->where('name', $name)->delete();
    }
}
