<?php

namespace App\Listeners;

use App\Events\RiskConversationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RiskConverstionListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RiskConversationEvent $event): void
    {
        $event->data ;
    }
}
