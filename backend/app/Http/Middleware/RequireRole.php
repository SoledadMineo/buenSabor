<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $rol = $usuario->empleado?->rol?->value;
        if (!$rol && $usuario->cliente) {
            $rol = 'cliente';
        }

        if (!$rol || !in_array($rol, $roles, true)) {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
