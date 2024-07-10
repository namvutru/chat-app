<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddFriend extends Model
{
    use HasFactory;
    protected $table = "add_friend";

    protected $fillable = [
        'user_send_id',
        'user_receive_id',
        'status',
    ];

    const STATUS = [
        'not_access' => 0,
        'access' => 1
    ];

    public function user_send()
    {
        return $this->belongsTo(User::class, 'user_send_id', 'id');
    }

    public function user_receive()
    {
        return $this->belongsTo(User::class, 'user_receive_id', 'id');
    }
}
