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
     * 3. Autentica con sesión web
     * 4. Redirige a dashboard
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
        $this->authService->authenticateWeb($user, $request->boolean('remember'));

        // 4️⃣ SEGURIDAD: Regenera la sesión
        $request->session()->regenerate();

        // 5️⃣ REDIRIGIR: Va al dashboard
        return redirect()
            ->intended('/dashboard')
            ->with('success', 'Inicio de sesión exitoso.');
    }
}
