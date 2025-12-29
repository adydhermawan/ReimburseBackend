<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-login middleware for development purposes only.
 * This will automatically authenticate the first user in the database.
 * 
 * DO NOT USE IN PRODUCTION!
 */
class AutoLoginDevelopment
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only auto-login if not already authenticated
        if (!Auth::check()) {
            $user = User::first();
            
            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
