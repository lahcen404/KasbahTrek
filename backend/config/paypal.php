<?php

return [

    'mode' => env('PAYPAL_MODE', 'sandbox'),

    'client_id' => env('PAYPAL_CLIENT_ID'),

    'secret' => env('PAYPAL_SECRET'),

    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    'currency' => strtoupper((string) env('PAYPAL_CURRENCY', 'USD')),

    'return_url' => env(
        'PAYPAL_RETURN_URL',
        rtrim((string) env('APP_URL', 'http://localhost'), '/').'/payment/paypal/return'
    ),

    'cancel_url' => env(
        'PAYPAL_CANCEL_URL',
        rtrim((string) env('APP_URL', 'http://localhost'), '/').'/payment/paypal/cancel'
    ),

];
