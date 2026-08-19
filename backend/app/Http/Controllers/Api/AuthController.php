<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * AuthController - Autenticación API con Sanctum
 *
 * Maneja autenticación para clientes API (móviles, SPA, etc).
 * Devuelve tokens JWT en lugar de sesiones web.
 * Delega lógica de validación en AuthService (compartida con Web).
 *
 * @see App\Http\Controllers\Web\LoginController - Para autenticación web
 * @see App\Services\AuthService - Lógica compartida
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Registrar un nuevo usuario y devolver token Sanctum.
     *
     * @unauthenticated
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        // Delegar creación en AuthService (compartida con Web)
        $user = $this->authService->createUser($validated);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Registro exitoso.', 201);
    }

    /**
     * Autenticar usuario con email/contraseña y devolver token Sanctum.
     *
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Validar credenciales usando AuthService (compartida con Web)
        $user = $this->authService->validateCredentials(
            $request->email,
            $request->password
        );

        // Revoca tokens anteriores con el mismo nombre (sesión única por dispositivo)
        $this->authService->revokeSameName($user, 'auth_token');

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Inicio de sesión exitoso.');
    }

    /**
     * Revocar el token actual del usuario autenticado.
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        $request->user()->tokens()->delete();
        Auth::forgetGuards();
        auth()->shouldUse('web');
        auth('web')->logout();

        return $this->success(null, 'Sesión cerrada correctamente.');
    }

    /**
     * Enviar enlace de restablecimiento de contraseña al email indicado.
     *
     * @unauthenticated
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->error(__($status), 422);
        }

        return $this->success(null, __($status));
    }

    /**
     * Restablecer la contraseña usando el token enviado por email.
     *
     * @unauthenticated
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->error(__($status), 422);
        }

        return $this->success(null, __($status));
    }
}

