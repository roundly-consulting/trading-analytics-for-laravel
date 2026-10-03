<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default decimal precision (bcmath scale)
    |--------------------------------------------------------------------------
    |
    | The number of decimal places used for every calculation when a run does
    | not set its own scale via ->scale(). Higher values trade speed for
    | precision. A whole number of at least 0; a blank value (KEY=) is not set,
    | so 10 applies; anything else (e.g. "ten" or "10.5") throws an
    | InvalidConfigurationException naming the key.
    |
    */

    'scale' => env('TRADING_ANALYTICS_SCALE', 10),

    /*
    |--------------------------------------------------------------------------
    | Default win-rate bucketing period
    |--------------------------------------------------------------------------
    |
    | The period win-rate-by-period buckets into when a run does not set its
    | own period via ->usingWinRatePeriod(). One of: daily, weekly, monthly.
    | A blank value (KEY=) is not set, so daily applies; anything else throws
    | an InvalidConfigurationException listing them.
    |
    */

    'win_rate_period' => env('TRADING_ANALYTICS_WIN_RATE_PERIOD', 'daily'),

];
