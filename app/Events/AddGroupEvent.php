<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddGroupEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $user_receive_id;
    public $room_id;
    public $room_name;
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
        $this->room_name = $data['room_name'];
    }

    public function broadcastWith()
    {
        return [
            'message' => 'bạn đã nhận được lời mời tham gia phòng  ' . $this->room_name,
            'id' => $this->id,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user' . $this->user_receive_id);
    }
}
