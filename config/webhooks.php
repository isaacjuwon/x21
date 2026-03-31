<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Webhook Queue
    |--------------------------------------------------------------------------
    | The queue name used for processing incoming webhooks asynchronously.
    */

    'queue' => env('WEBHOOK_QUEUE', 'webhooks'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    | Per-provider settings. Add new providers here as needed.
    */

    'epins' => [
        'transaction_model' => \App\Models\TopupTransaction::class,
    ],

];
