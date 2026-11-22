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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'otakudesu' => [
        'base_url' => env('OTAKUDESU_API_BASE_URL', in_array(env('APP_ENV'), ['local', 'testing'], true) ? 'http://127.0.0.1:3000/api' : ''),
        'source_url' => env('OTAKUDESU_SOURCE_URL', 'https://otakudesu.blog'),
        'disable_proxy' => env('OTAKUDESU_DISABLE_PROXY', true),
        'proxy_images' => env('OTAKUDESU_PROXY_IMAGES', env('APP_ENV') !== 'local'),
        'warm_pages' => max(1, (int) env('OTAKUDESU_WARM_PAGES', 5)),
        'warm_genres' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'OTAKUDESU_WARM_GENRES',
            'action,fantasy,romance,comedy,school,adventure,drama,shounen,supernatural,slice-of-life'
        ))))),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'base_url' => env(
            'MIDTRANS_API_BASE_URL',
            env('MIDTRANS_IS_PRODUCTION', false)
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com'
        ),
        'webhook_path' => env('MIDTRANS_WEBHOOK_PATH', '/payments/midtrans/webhook'),
    ],

];
