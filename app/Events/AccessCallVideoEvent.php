<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessCallVideoEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_send_id;
    public $user_receive_id;
    public $user_receive_name;
    public $answer;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->user_receive_id =  $data['user_receive_id'];
        $this->user_send_id =  $data['user_send_id'];
        $this->user_receive_name =  $data['user_receive_name'];
        $this->answer =  $data['answer'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */

    public function broadcastWith()
    {
        return [
            'message' => $this->user_receive_name . ' đã chấp nhận cuộc gọi của bạn',
            'user_receive_name' => $this->user_receive_name,
            'user_receive_id' => $this->user_receive_id,
            'answer' => $this->answer,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('user' . $this->user_send_id);
    }
}
