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

    'iracing' => [
        'oauth_base_url' => env('IRACING_OAUTH_BASE_URL', 'https://oauth.iracing.com'),
        'api_base_url' => env('IRACING_API_BASE_URL', 'https://members-ng.iracing.com'),
        'client_id' => env('IRACING_CLIENT_ID'),
        'client_secret' => env('IRACING_CLIENT_SECRET'),
        'redirect_uri' => env('IRACING_REDIRECT_URI'),
        'server_username' => env('IRACING_SERVER_USERNAME'),
        'server_password' => env('IRACING_SERVER_PASSWORD'),
    ],

];
