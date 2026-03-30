<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minimum Account Age (Days)
    |--------------------------------------------------------------------------
    |
    | The minimum number of days a user's account must exist before they are
    | eligible to apply for a loan.
    |
    */

    'min_account_age_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Loan Levels
    |--------------------------------------------------------------------------
    |
    | Maps each loan level to its maximum allowable loan amount. Users are
    | assigned a level, and their requested amount must not exceed the limit
    | for their level.
    |
    */

    'levels' => [
        1 => 5000,
        2 => 15000,
        3 => 50000,
    ],

];
