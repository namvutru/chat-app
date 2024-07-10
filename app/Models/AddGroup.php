<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddGroup extends Model
{
    use HasFactory;

    protected $table = "add_group";

    protected $fillable = [
        'user_receive_id',
        'room_id',
        'status',
    ];

    const STATUS = [
        'not_access' => 0,
        'access' => 1
    ];

    public function user_receive()
    {
        return $this->belongsTo(User::class, 'user_receive_id', 'id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
}
