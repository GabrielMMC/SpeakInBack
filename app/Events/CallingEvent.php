<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room_id;
    public $rtc_response;
    public $type;

    /**
     * Create a new event instance.
     */
    public function __construct($room_id, $rtc_response, $type)
    {
        $this->room_id = $room_id;
        $this->rtc_response = $rtc_response;
        $this->type = $type;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('call.channel.' . $this->room_id);
    }

    public function broadcastAs()
    {
        return 'CallingEvent';
    }

    public function broadcastWith()
    {

        return [
            'room_id' => $this->room_id,
            'response' => $this->rtc_response,
            'type' => $this->type,
            'user_id' => auth()->user()->id,
        ];
    }
}
