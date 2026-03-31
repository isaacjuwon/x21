<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Provider
    |--------------------------------------------------------------------------
    |
    | This value determines the default API provider that will be used by
    | the ApiManager. This can be changed at runtime if needed.
    |
    */

    'default' => env('API_DEFAULT_PROVIDER', 'paystack'),

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
    ],

];
