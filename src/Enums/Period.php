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

    public function bucketFor(Carbon $moment): string
    {
        return match ($this) {
            self::DAILY => $moment->format('Y-m-d'),
            self::WEEKLY => $moment->format('o-\WW'),
            self::MONTHLY => $moment->format('Y-m'),
        };
    }
}
