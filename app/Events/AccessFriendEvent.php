<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessFriendEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $user_receive_id;
    public $user_send_id;
    public $user_receive_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->user_send_id = $data['user_send_id'];
        $this->user_receive_id = $data['user_receive_id'];
        $this->user_receive_name = $data['user_receive_name'];
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->user_receive_name . ' đã chấp lời mời kết bạn',
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user' . $this->user_send_id);
    }
}
