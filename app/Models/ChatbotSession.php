<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $fillable = ['session_id', 'last_project', 'last_intent'];

    public static function getOrCreate($sessionId)
    {
        return static::firstOrCreate(
            ['session_id' => $sessionId],
            ['last_project' => null, 'last_intent' => null]
        );
    }
}