<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Verification;
use Illuminate\Support\Facades\Auth;
class VerificationObserver
{
    /**
     * Handle the Verification "created" event.
     *
     * @param  \App\Models\Verification  $verification
     * @return void
     */
    public function created(Verification $verification)
    {
        Log::create([
            'user_id' => $verification->user_id,
            'admin_id' => null,
            'name' => $verification->user->name,
            'email' => $verification->user->email,
            'details'=>json_encode($verification)
        ]);
    }

    /**
     * Handle the Verification "updated" event.
     *
     * @param  \App\Models\Verification  $verification
     * @return void
     */
    public function updated(Verification $verification)
    {
        Log::create([
            'user_id' => $verification->user_id,
            'admin_id' => Auth::id(),
            'name' => $verification->user->name,
            'email' => $verification->user->email,
            'details'=>json_encode($verification)
        ]);
    }

    /**
     * Handle the Verification "deleted" event.
     *
     * @param  \App\Models\Verification  $verification
     * @return void
     */
    public function deleted(Verification $verification)
    {
        Log::create([
            'user_id' => $verification->user_id,
            'admin_id' => Auth::id(),
            'name' => $verification->user->name,
            'email' => $verification->user->email,
            'details'=>json_encode($verification)
        ]);
    }

    /**
     * Handle the Verification "restored" event.
     *
     * @param  \App\Models\Verification  $verification
     * @return void
     */
    public function restored(Verification $verification)
    {
        //
    }

    /**
     * Handle the Verification "force deleted" event.
     *
     * @param  \App\Models\Verification  $verification
     * @return void
     */
    public function forceDeleted(Verification $verification)
    {
        //
    }
}
