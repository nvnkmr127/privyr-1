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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
        'webhook_verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),
    ],

    'watxio' => [
        // Watxio WhatsApp platform. Unset = WhatsApp sends are logged, not dispatched.
        'endpoint' => env('WATXIO_ENDPOINT'),
        'token' => env('WATXIO_TOKEN'),
        'new_lead_template' => env('WATXIO_NEW_LEAD_TEMPLATE',
            'Hi {name}, thanks for reaching out regarding {title}. Our team will contact you shortly.'),
    ],

    'push' => [
        // Firebase Cloud Messaging HTTP v1 for instant new-lead alerts.
        // Unset credentials = push is logged, not dispatched, so the CRM works ungated.
        'driver' => env('PUSH_DRIVER', 'fcm'),
        // Absolute path to a Firebase service-account JSON key file.
        'credentials' => env('FCM_CREDENTIALS'),
        // Optional; falls back to the project_id inside the service-account file.
        'project_id' => env('FCM_PROJECT_ID'),
        // v1 send endpoint. {project} is substituted at runtime.
        'endpoint' => env('FCM_ENDPOINT', 'https://fcm.googleapis.com/v1/projects/{project}/messages:send'),
    ],

    'lead_capture' => [
        // Optional shared secret for generic webhooks (IndiaMART/JustDial/custom).
        // When set, generic capture calls must send it as ?key= or X-Capture-Secret.
        // FB/Google are exempt — they authenticate via their own webhook flows.
        'secret' => env('LEAD_CAPTURE_SECRET'),
    ],

];
