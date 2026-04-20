<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    'currency' => strtolower((string) env('STRIPE_CURRENCY', 'mad')),

    'success_url' => env(
        'STRIPE_SUCCESS_URL',
        rtrim((string) env('APP_URL', 'http://localhost'), '/').'/payment/success?session_id={CHECKOUT_SESSION_ID}'
    ),

    'cancel_url' => env(
        'STRIPE_CANCEL_URL',
        rtrim((string) env('APP_URL', 'http://localhost'), '/').'/payment/cancel'
    ),

];
