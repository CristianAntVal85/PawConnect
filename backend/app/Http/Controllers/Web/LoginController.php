<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * LoginController - Autenticación web con sesiones
 *
 * Maneja login para vistas Blade.
 * Delega lógica de validación en AuthService (compartida con API).
 *
 * @see App\Http\Controllers\Api\AuthController - Para autenticación API
 * @see App\Services\AuthService - Lógica compartida
 */
class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Procesar el login del usuario a través del formulario web.
     *
     * 1. Valida email y contraseña
     * 2. Valida credenciales (delegado en AuthService)
     * 3. Autentica con sesión web + crea token Sanctum
     * 4. Devuelve JSON si es AJAX, o redirige si es formulario tradicional
     */
    public function store(Request $request)
    {
        // 1️⃣ VALIDAR: Email y contraseña son requeridos
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2️⃣ AUTENTICAR: Valida credenciales usando servicio compartido
        try {
            $user = $this->authService->validateCredentials(
                $validated['email'],
                $validated['password']
            );
        } catch (ValidationException $e) {
            throw $e;
        }

        // 3️⃣ SESIÓN: Autentica con sesión web (remember si está checked)
        // Auth::login() regenera la sesión automáticamente
        $this->authService->authenticateWeb($user, $request->boolean('remember'));

        // 4️⃣ CREAR TOKEN: Genera token Sanctum para requests AJAX a la API
        // Revoca tokens anteriores con el mismo nombre (sesión única por dispositivo)
        $this->authService->revokeSameName($user, 'auth_token');
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5️⃣ RESPONDER: JSON si es AJAX, HTML redirect si es formulario tradicional
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
        }

        // Redirigir a dashboard (para navegadores tradicionales sin JavaScript)
        if ($user->role === 'admin') {
            return redirect()->intended('/admin/dashboard')
                ->with('auth_token', $token);
        }
        return redirect()
            ->intended('/dashboard')
            ->with('auth_token', $token);
    }
}
