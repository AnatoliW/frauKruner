<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function redirectTo(?Authenticatable $user = null): string
    {
        if (! $user || ! isset($user->role_id)) {
            return '/';
        }

        switch ((int) $user->role_id) {
            case 1:
                return '/admin';
            case 2:
                return '/user';
            case 3:
                return '/seller';
            default:
                return '/';
        }
    }

    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->to($this->redirectTo(Auth::guard($guard)->user()));
            }
        }

        return $next($request);
    }
}
