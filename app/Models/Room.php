<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    use HasFactory;
    protected $table = 'rooms';
    protected $fillable = [
        'name',
        'type'
    ];

    const TYPE = [
        'global' => 1,
        'group' => 2,
        'friend' => 3
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'room_id', 'id')->orderBy('created_at', 'asc');
    }

    public function lastmessage()
    {
        return $this->hasOne(Message::class, 'room_id', 'id')->orderBy('created_at', 'desc');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'usertoroom', 'room_id', 'user_id');
    }
}
