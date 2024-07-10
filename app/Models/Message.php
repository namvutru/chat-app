<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';

    protected $fillable = [
        'user_id',
        'message',
        'room_id',
        'status'
    ];

    const STATUS = [
        'hide' => 0,
        'show' => 1,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(MessageFile::class, 'message_id', 'id');
    }
}
