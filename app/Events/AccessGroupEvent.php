<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessGroupEvent implements ShouldBroadcast

{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $user_receive_id;
    public $room_id;
    public $user_receive_name;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->room_id = $data['room_id'];
        $this->user_receive_id = $data['user_receive_id'];
        $this->user_receive_name = $data['user_receive_name'];
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->user_receive_name .  'đã tham gia vào phòng',
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('room' . $this->room_id);
    }
}
