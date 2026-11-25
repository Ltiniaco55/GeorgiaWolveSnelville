<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Billing rule: the cut-off day
    |--------------------------------------------------------------------------
    |
    | Join from the 1st up to this day (included) and you pay the current month
    | now. Join after it and the rest of the month is free: the first monthly
    | charge is on the 1st of the next month. Single source of truth for the
    | rule: the home calculator, the plan notes and (phase 7) Cashier read it.
    |
    */

    'cutoff_day' => 15,

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | The club's timezone: "today" and the cut-off day are counted here, not in
    | UTC or in the visitor's timezone.
    |
    */

    'timezone' => 'America/New_York',

    /*
    |--------------------------------------------------------------------------
    | Prices
    |--------------------------------------------------------------------------
    |
    | In cents of USD. Sample prices from the old website; the annual plan is
    | pending confirmation by the client. The uniform is a one-time payment.
    |
    */

    'currency' => 'usd',

    'prices' => [
        'free_trial' => 0,
        'monthly' => 10_000,
        'annual' => 100_000,
        'uniform' => 8_000,
    ],

];
