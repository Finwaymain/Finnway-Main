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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
    ],

    'firebase' => [
        'project_id'          => env('FIREBASE_PROJECT_ID', 'fiinway-app'),
        'credentials_path'    => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/credentials.json')),
        'credentials_base64'  => env('FIREBASE_CREDENTIALS_BASE64'),
        'vapid_key'           => env('FIREBASE_VAPID_KEY', 'BBexvMFALVzybQ6CoOoHwJwC1N4JI_QQ7HIeGGr9rhDm80k0HMz7FGCLBc5K5TsADMmJwVcnV3HlenGseSnF6UI'),
        'sender_id'           => env('FIREBASE_SENDER_ID', '907739123740'),
    ],

];
