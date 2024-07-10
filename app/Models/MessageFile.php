<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    use HasFactory;

    protected $table = 'message_files';
    protected $fillable = [
        'url',
        'type_file',
        'message_id',
    ];

    const TYPE = [
        'image' => 1,
        'video' => 2,
        'other' => 3
    ];
}
