<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected const ROLES = [
        'admin' => 1,
        'user' => 2,
        'seller' => 3,
    ];

    public function handle(Request $request, Closure $next, string $role = 'admin')
    {
        $user = Auth::user();

        if (! $user || ! isset(self::ROLES[$role])) {
            abort(403, 'Zugriff verboten');
        }

        if (self::ROLES[$role] === (int) $user->role_id) {
            return $next($request);
        }

        abort(403, 'Zugriff verboten');
    }
}
