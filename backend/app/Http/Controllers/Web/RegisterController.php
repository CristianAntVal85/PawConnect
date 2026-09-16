<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * 3. Autentica automáticamente + crea token Sanctum
     * 4. Devuelve JSON si es AJAX, o redirige si es formulario tradicional
     */
    public function store(Request $request)
    {
        // 1️⃣ VALIDAR: Nombre, email, contraseña y photo
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'photo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // 2️⃣ CREAR: Usuario nuevo (delegado en AuthService)
        $user = $this->authService->createUser($validated);

        // 3️⃣ GUARDAR: Foto de perfil si se subió
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $user->update(['photo' => $path]);
        }

        // 4️⃣ AUTENTICAR: Login automático
        $this->authService->authenticateWeb($user, remember: false);

        // 5️⃣ SESIÓN: Regenera la sesión
        $request->session()->regenerate();

        // 6️⃣ CREAR TOKEN: Genera token Sanctum para requests AJAX a la API
        $token = $user->createToken('auth_token')->plainTextToken;

        // 7️⃣ RESPONDER: JSON si es AJAX, HTML redirect si es formulario tradicional
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registro exitoso. ¡Bienvenido a PawConnect!',
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
        return redirect()
            ->route('web.dashboard')
            ->with('auth_token', $token)
            ->with('success', 'Registro exitoso. ¡Bienvenido a PawConnect!');
    }
}
