<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY', '6LfEiisuAAAAAM1dgo05Kv4IqPwYUF5NTWbDUN9V'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY', '6LfEiisuAAAAAEPuGqfaxFfGOtUGHNdZq0djWVkd'),
    ],

    'jwt' => [
        'secret' => env('JWT_SECRET', env('APP_KEY')),
        'ttl' => (int) env('JWT_TTL', 86400),
    ],

    'flowkirim' => [
        'api_key' => env('FLOWKIRIM_API_KEY', 'fk_live_25ebb5646a8d7afd3d9e526d394f81098a8e48b0ed355b0a'),
        'base_url' => env('FLOWKIRIM_BASE_URL', 'https://scan.flowkirim.com/api/v1/verify'),
        'channel' => env('FLOWKIRIM_DEFAULT_CHANNEL', 'whatsapp_tap'),
        'brand' => env('FLOWKIRIM_BRAND', 'KLINIKU'),
        'ttl' => (int) env('FLOWKIRIM_TTL', 300),
    ],

];
