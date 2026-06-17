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
    | precision.
    |
    */

    'scale' => (int) env('TRADING_ANALYTICS_SCALE', 10),

    /*
    |--------------------------------------------------------------------------
    | Default win-rate bucketing period
    |--------------------------------------------------------------------------
    |
    | The period win-rate-by-period buckets into when a run does not set its
    | own period via ->usingWinRatePeriod(). One of: daily, weekly, monthly.
    | An unrecognised value falls back to daily.
    |
    */

    'win_rate_period' => env('TRADING_ANALYTICS_WIN_RATE_PERIOD', 'daily'),

];
