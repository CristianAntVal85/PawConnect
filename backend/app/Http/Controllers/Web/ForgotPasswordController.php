<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/**
 * ForgotPasswordController - Recuperación de contraseña (web)
 *
 * Maneja envío de enlace de recuperación para vistas Blade.
 * Usa Password facade de Laravel (igual que API).
 *
 * @see App\Http\Controllers\Api\AuthController - Para reset API
 */
class ForgotPasswordController extends Controller
{
    /**
     * Enviar enlace de restablecimiento de contraseña al email indicado.
     *
     * 1. Valida email
     * 2. Envía enlace por email
     * 3. Redirige con mensaje
     */
    public function store(Request $request)
    {
        // 1️⃣ VALIDAR: Email es requerido
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // 2️⃣ ENVIAR: Enlace de recuperación
        $status = Password::sendResetLink($request->only('email'));

        // 3️⃣ REDIRIGIR: Con mensaje
        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', __($status));
        }

        return back()->with('success', __($status));
    }
}
