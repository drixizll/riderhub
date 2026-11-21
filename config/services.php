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

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'livemode' => env('PAYMONGO_LIVEMODE', false),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com'),
        'payment_method_types' => ['card', 'gcash', 'paymaya'],
    ],

    'repohive' => [
        'ai_key' => env('REPOHIVE_AI_KEY', 'hbc_ai_e6b17f6a201164aa22706f6517d574c1d0b5a4d786a5d42c'),
        'ai_url' => env('REPOHIVE_AI_URL', 'https://apicloud.repohive.com/api/ai/chat'),
        'ai_model' => env('REPOHIVE_AI_MODEL', 'gpt-4o-mini'),
        'otp_key' => env('REPOHIVE_OTP_KEY'),
        'otp_url' => env('REPOHIVE_OTP_URL', 'https://cloud.repohive.com/api/v1/otp'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

];
