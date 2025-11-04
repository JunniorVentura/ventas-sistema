<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermisoMiddleware
{
    public function handle(Request $request, Closure $next, string $permiso)
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->tienePermiso($permiso)) {
            return response()->json(['error' => 'No tienes permiso para acceder.'], 403);
        }

        return $next($request);
    }
}