<?php

return [
    'tickets' => [
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'api_key'  => env('OPENAI_API_KEY'),
        'model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout'  => (int)env('OPENAI_TIMEOUT', 45),
    ],
];
