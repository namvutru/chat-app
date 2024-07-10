<?php

namespace App\Jobs;

use App\Events\GotMessage;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Message $message)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        GotMessage::dispatch([
            'id' => $this->message->id,
            'user_id' => $this->message->user_id,
            'room_id' => $this->message->room_id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at,
        ]);
    }
}
