<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        
        $isAdmin = $request->header('X-User-Admin');

        if (!$isAdmin || $isAdmin !== 'true') {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        return $next($request);
    }
}
