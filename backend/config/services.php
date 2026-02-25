<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'coingecko' => [
        'base_url' => env('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3'),
        'timeout_seconds' => (float) env('COINGECKO_TIMEOUT_SECONDS', 3),
        'retry_times' => (int) env('COINGECKO_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('COINGECKO_RETRY_SLEEP_MS', 150),
        'cache_ttl_markets' => (int) env('COINGECKO_CACHE_TTL_MARKETS', 30),
        'cache_ttl_coin' => (int) env('COINGECKO_CACHE_TTL_COIN', 30),
    ],

];
