<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Procesar el login del usuario a través del formulario web.
     * Valida email y contraseña, autentica y redirige.
     */
    public function store(Request $request)
    {
        // 1️⃣ VALIDAR: Email y contraseña son requeridos
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2️⃣ AUTENTICAR: Intenta loguear con las credenciales
        if (!Auth::attempt($validated, $request->boolean('remember'))) {
            // Si falla, redirige de vuelta al login con error
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        // 3️⃣ SESIÓN: Regenera la sesión por seguridad
        $request->session()->regenerate();

        // 4️⃣ REDIRIGIR: Va al dashboard
        return redirect()
            ->intended('/dashboard')
            ->with('success', 'Inicio de sesión exitoso.');
    }
}
