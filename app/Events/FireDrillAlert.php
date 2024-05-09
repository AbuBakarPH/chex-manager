<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FireDrillAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data ;
    }

    public function broadcastWith()
    {
        return $this->data;
    }

    public function broadcastOn()
    {
        return new Channel('fire');
    }

    public function broadcastAs()
    {
        return 'fire';
    }
}
