<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = env('INTERNAL_API_SECRET', 'recashly_internal_secret_2024');
        
        if ($request->header('X-Internal-Secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized Access'], 401);
        }

        return $next($request);
    }
}
