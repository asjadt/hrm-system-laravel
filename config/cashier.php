<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key and secret key give you access to Stripe's
    | API. The "publishable" key is typically used when interacting with
    | Stripe.js while the "secret" key accesses private API endpoints.
    |
    */

    'stripe' => [
        'model' => App\Models\User::class,

        'key' => env('STRIPE_KEY'),

        'secret' => env('STRIPE_SECRET'),

        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
            'controller' => \App\Http\Controllers\CustomWebhookController::class,
        ],

    ],

];
