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
                        break;
                    }
                }
            } else {
                Auth::shouldUse('sanctum');
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}
