<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

it('buckets a moment per period', function (Period $period, string $expected) {
    $moment = Carbon::create(2024, 8, 6, 14, 30); // a Tuesday

    expect($period->bucketFor($moment))->toBe($expected);
})->with([
    [Period::DAILY, '2024-08-06'],
    [Period::WEEKLY, '2024-W32'],
    [Period::MONTHLY, '2024-08'],
]);
