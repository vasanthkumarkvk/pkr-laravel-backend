<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

 'groq' => [
    'key'   => env('GROQ_API_KEY'),
    'url'   => env('GROQ_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),  // ← default change
],
];