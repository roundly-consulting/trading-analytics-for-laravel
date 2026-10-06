<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Enums;

use Illuminate\Support\Carbon;
use RoundlyConsulting\Enums\Helpers;

enum Period: string
{
    use Helpers;

    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    /**
     * The calendar bucket a moment falls in, read in the default timezone (Laravel sets it from
     * `app.timezone`). In its own offset, the same instant written as `+00:00` and `-05:00` landed
     * in two buckets, each on a different calendar.
     */
    public function bucketFor(Carbon $moment): string
    {
        $moment = $moment->copy()->setTimezone(date_default_timezone_get());

        return match ($this) {
            self::DAILY => $moment->format('Y-m-d'),
            self::WEEKLY => $moment->format('o-\WW'),
            self::MONTHLY => $moment->format('Y-m'),
        };
    }
}
