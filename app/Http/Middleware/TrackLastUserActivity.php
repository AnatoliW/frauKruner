<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackLastUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      
        if (Auth::check()) {
            $user = Auth::user();
           
            if ($user->last_login_at && now()->diffInMinutes($user->last_login_at) >= 60) {
             
                Auth::logout(); 
                $request->session()->invalidate(); 
                $request->session()->regenerateToken(); 
                return redirect('/login')->withErrors(['You have been logged out due to inactivity.']);
            }
            $user->last_login_at = now();
            $user->save();
        }

        return $next($request);
    }
}
