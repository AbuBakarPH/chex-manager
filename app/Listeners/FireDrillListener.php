<?php

namespace App\Listeners;

use App\Events\FireDrillAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FireDrillListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     */
    public function handle(FireDrillAlert $event): void
    {
        $event ;
    }
}
