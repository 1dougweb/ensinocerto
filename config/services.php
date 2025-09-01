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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google' => [
        'root_folder_id' => env('GOOGLE_DRIVE_ROOT_FOLDER_ID'),
        'service_account' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT', false),
        'test_mode' => env('GOOGLE_DRIVE_TEST_MODE', false),
        'shared_drive_id' => env('GOOGLE_DRIVE_SHARED_DRIVE_ID'),
        'client_id' => env('GOOGLE_OAUTH_CLIENT_ID'),
        'client_secret' => env('GOOGLE_OAUTH_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_OAUTH_REDIRECT_URI', 'http://localhost:8000/auth/google/callback'),
    ],

    'google_analytics' => [
        'view_id' => env('GOOGLE_ANALYTICS_VIEW_ID'),
        'service_account_key_file' => env('GOOGLE_ANALYTICS_SERVICE_ACCOUNT_KEY_FILE'),
        'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
        'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),
        'gtm_id' => env('GOOGLE_TAG_MANAGER_ID'),
        'access_token' => env('GOOGLE_ANALYTICS_ACCESS_TOKEN'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'client_id' => env('GOOGLE_ANALYTICS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ANALYTICS_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_ANALYTICS_REFRESH_TOKEN'),
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
    ],

];
