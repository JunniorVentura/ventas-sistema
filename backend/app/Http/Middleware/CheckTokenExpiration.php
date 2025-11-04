<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->token_expiration && now()->greaterThan($user->token_expiration)) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Token expirado. Por favor inicie sesión nuevamente.',
            ], 401);
        }

        return $next($request);
    }
}
