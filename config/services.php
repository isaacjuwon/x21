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

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'url' => env('PAYSTACK_URL', 'https://api.paystack.co'),
    ],

    'epins' => [
        'api_key' => env('EPINS_API_KEY'),
        'url' => env('EPINS_URL', 'https://api.epins.com.ng/core'),
        'sandbox_url' => env('EPINS_SANDBOX_URL', 'https://sandbox.epins.com.ng/core'),
    ],

    'dojah' => [
        'api_key' => env('DOJAH_API_KEY'),
        'app_id' => env('DOJAH_APP_ID'),
        'base_url' => env('DOJAH_BASE_URL', 'https://api.dojah.io'),
    ],

];
