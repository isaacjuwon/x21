<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Share Holding Period
    |--------------------------------------------------------------------------
    |
    | This value determines the number of days a user must hold shares before
    | they are eligible to sell them. The value is managed via the admin panel
    | using the ShareSettings spatie/laravel-settings class.
    |
    */

    'holding_period_days' => env('SHARES_HOLDING_PERIOD_DAYS', 30),

];
