<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    protected $fillable = [
        'session_id',
        'user_message',
        'bot_reply',
        'language',
        'matched_by',
    ];
}