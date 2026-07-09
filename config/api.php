<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Providers
    |--------------------------------------------------------------------------
    |
    | These values determine the default API providers that will be used by
    | the ApiManager for specific feature types. This can be changed at runtime if needed.
    |
    */

    'default' => env('API_DEFAULT_PROVIDER', 'paystack'),

    'defaults' => [
        'payment' => env('API_PAYMENT_PROVIDER', 'paystack'),
        'vtu' => env('API_VTU_PROVIDER', 'epins'),
        'account' => env('API_ACCOUNT_PROVIDER', 'paystack'),
        'verification' => env('API_VERIFICATION_PROVIDER', 'dojah'),
        'sms' => env('API_SMS_PROVIDER', 'kudisms'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the API providers for your application.
    | Each provider should have a unique name and a driver.
    |
    */

    'providers' => [
        'paystack' => [
            'driver' => 'paystack',
        ],
        'dojah' => [
            'driver' => 'dojah',
        ],
        'epins' => [
            'driver' => 'epins',
        ],
        'monnify' => [
            'driver' => 'monnify',
        ],
        'kudisms' => [
            'driver' => 'kudisms',
        ],
    ],

];
