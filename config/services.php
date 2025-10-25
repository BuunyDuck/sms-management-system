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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio SMS Service
    |--------------------------------------------------------------------------
    |
    | Configuration for Twilio SMS integration. Get your credentials from:
    | https://console.twilio.com
    |
    */
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER', '+14062152048'),
        'webhook_url' => env('TWILIO_WEBHOOK_URL', env('APP_URL') . '/webhook/twilio'),
        'validate_signature' => env('TWILIO_VALIDATE_SIGNATURE', true),
        
        // Available phone numbers for sending SMS
        'from_numbers' => [
            '+14062152048' => 'Main (Monitored)',
            '+14066076333' => 'Unmonitored',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Integrations
    |--------------------------------------------------------------------------
    |
    | URLs for external Montana Sky systems
    |
    */
    'integrations' => [
        'ticket_system_url' => env('TICKET_SYSTEM_URL', 'https://www.montanasky.net/MyAccount/TicketTracker/'),
        'customer_portal_url' => env('CUSTOMER_PORTAL_URL', 'https://www.montanasky.net/MyAccount/'),
        'internal_api_url' => env('INTERNAL_API_URL', 'https://admin01.montanasat.net/api/twilio/sms/send'),
    ],

];
