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

    // WARN-01 FIX: Chuyển cấu hình cổng thanh toán vào config/services.php
    // để PaymentService dùng config() thay vì env() trực tiếp.
    // env() trả về null sau khi chạy `php artisan config:cache` trên production.
    'vnpay' => [
        'env'            => env('VNP_ENV', 'sandbox'),
        'tmn_code'       => env('VNP_TMN_CODE'),
        'hash_secret'    => env('VNP_HASH_SECRET'),
        'sandbox_url'    => env('VNP_SANDBOX_URL', env('VNP_URL')),
        'production_url' => env('VNP_PRODUCTION_URL', env('VNP_URL')),
        'sandbox_return_url'    => env('VNP_SANDBOX_RETURN_URL', env('VNP_RETURN_URL')),
        'production_return_url' => env('VNP_PRODUCTION_RETURN_URL', env('VNP_RETURN_URL')),
    ],

    'momo' => [
        'env'             => env('MOMO_ENV', 'sandbox'),
        'partner_code'    => env('MOMO_PARTNER_CODE'),
        'access_key'      => env('MOMO_ACCESS_KEY'),
        'secret_key'      => env('MOMO_SECRET_KEY'),
        'request_type'    => env('MOMO_REQUEST_TYPE', 'captureWallet'),
        'sandbox_endpoint'    => env('MOMO_SANDBOX_API_ENDPOINT', env('MOMO_API_ENDPOINT')),
        'production_endpoint' => env('MOMO_PRODUCTION_API_ENDPOINT', env('MOMO_API_ENDPOINT')),
        'sandbox_redirect_url'    => env('MOMO_SANDBOX_REDIRECT_URL', env('MOMO_REDIRECT_URL')),
        'production_redirect_url' => env('MOMO_PRODUCTION_REDIRECT_URL', env('MOMO_REDIRECT_URL')),
        'sandbox_ipn_url'    => env('MOMO_SANDBOX_IPN_URL', env('MOMO_IPN_URL')),
        'production_ipn_url' => env('MOMO_PRODUCTION_IPN_URL', env('MOMO_IPN_URL')),
    ],

];

