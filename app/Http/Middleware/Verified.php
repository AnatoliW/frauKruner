<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Verified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ((int) $user->verified === 1 || $user->verification) {
            $user->update([
                'last_login_at' => Carbon::now(),
                'email_send_at' => null,
            ]);

            return $next($request);
        }

        return redirect()->route('seller.verification');
    }
}
