<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddFriendNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $user_receive_id;
    public $user_send_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_send_id)
    {
        $this->user_send_id = $user_send_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function broadcastWith()
    {
        return [
            'message' => 'kết bạn với tôi',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new PrivateChannel('user' . $this->user_receive_id);
    }
}
