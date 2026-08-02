<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'No autorizado. Se requiere rol administrador.',
                ], 403);
            }

            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
