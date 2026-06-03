<?php

namespace App\Listeners;

use App\Events\StatusProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class StatusActive
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\StatusProcessed  $event
     * @return void
     */
    public function handle(StatusProcessed $event)
    {
    
    }
}
