<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        Log::create([
            'user_id' => $user->id,
            'admin_id' => null,
            'name' => $user->name,
            'email' => $user->email,
            'details'=>json_encode($user)
        ]);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        // dd($user);
        if(auth()->check()){
            Log::create([
               'user_id' => $user->id,
               'admin_id' => Auth::id(),
               'name' => Auth::user()->name,
               'email' => $user->email,
               'details'=>json_encode($user)
           ]);
        }

    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        if(auth()->check()){
            Log::create([
               'user_id' => $user->id,
               'admin_id' => Auth::id(),
               'name' => Auth::user()->name,
               'email' => $user->email,
               'details'=>'User deleted'
           ]);
        }
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
