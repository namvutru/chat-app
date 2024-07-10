<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallVideoEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_receive_id;
    public $user_send_id;
    public $user_send_name;
    public $user_send_peer_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->user_receive_id =  $data['user_receive_id'];
        $this->user_send_id =  $data['user_send_id'];
        $this->user_send_name =  $data['user_send_name'];
        $this->user_send_peer_id =  $data['user_send_peer_id'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastWith()
    {
        return [
            'message' => 'bạn đã nhận được cuộc gọi từ ' . $this->user_send_name,
            'user_send_id' => $this->user_send_id,
            'user_send_name' => $this->user_send_name,
            'user_send_peer_id' => $this->user_send_peer_id
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user' . $this->user_receive_id);
    }
}
