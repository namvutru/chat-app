<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = [
        'text',
        'type_notification',
        'status',
        'user_id',
    ];

    const TYPE = [
        'message' => 1,
        'warning' => 2,
        'other' => 3,
    ];
}
