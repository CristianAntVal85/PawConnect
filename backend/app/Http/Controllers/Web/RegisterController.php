<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * RegisterController - Registro web con sesiones
 *
 * Maneja registro para vistas Blade.
 * Delega lógica de creación de usuario en AuthService (compartida con API).
 *
 * @see App\Http\Controllers\Api\AuthController - Para registro API
 * @see App\Services\AuthService - Lógica compartida
 */
class RegisterController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Procesar el registro del usuario a través del formulario web.
     *
     * 1. Valida nombre, email y contraseña
     * 2. Crea usuario (delegado en AuthService)
     * 3. Autentica automáticamente
     * 4. Redirige a dashboard
     */
    public function store(Request $request)
    {
        // 1️⃣ VALIDAR: Nombre, email y contraseña
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        // 2️⃣ CREAR: Usuario nuevo (delegado en AuthService)
        $user = $this->authService->createUser($validated);

        // 3️⃣ AUTENTICAR: Login automático
        $this->authService->authenticateWeb($user, remember: false);

        // 4️⃣ SESIÓN: Regenera la sesión
        $request->session()->regenerate();

        // 5️⃣ REDIRIGIR: Va al dashboard
        return redirect()
            ->route('web.dashboard')
            ->with('success', 'Registro exitoso. ¡Bienvenido a PawConnect!');
    }
}
