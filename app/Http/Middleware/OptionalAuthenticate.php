<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalAuthenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        try {
            if (!empty($guards)) {
                foreach ($guards as $guard) {
                    if (Auth::guard($guard)->check()) {
                        // Usuário autenticado com sucesso
                        break;
                    }
                }
            } else {
                Auth::shouldUse('sanctum'); // usa o guard padrão se necessário
            }
        } catch (\Exception $e) {
            // falha na autenticação é ignorada
        }

        return $next($request);
    }
}
