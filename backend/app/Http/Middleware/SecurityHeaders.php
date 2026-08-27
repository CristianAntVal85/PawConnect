<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Agregar headers de seguridad HTTP recomendados por OWASP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Habilitar filtro XSS del navegador
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Forzar HTTPS en producción (HSTS)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy - restrictivo pero funcional
        // En desarrollo, permitir Vite en localhost:5173
        $viteDevServer = app()->environment('local', 'development') ? ' http://localhost:5173 ws://localhost:5173' : '';
        $googleFonts = ' https://fonts.googleapis.com';

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://cdn.jsdelivr.net{$viteDevServer}",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com{$googleFonts}{$viteDevServer}",
            "img-src 'self' data: https: blob:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self' https://api.stripe.com wss://localhost:8080 ws://localhost:8080{$viteDevServer}",
            "frame-src 'self' https://js.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "upgrade-insecure-requests",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Política de referrer - no enviar información sensible
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (antes Feature-Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=(), camera=()');

        return $response;
    }
}
