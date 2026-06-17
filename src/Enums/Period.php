<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Enums;

use Illuminate\Support\Carbon;

enum Period: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    /**
     * The string value of every case, handy for building selects or validation
     * rules in a host app.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $period): string => $period->value, self::cases());
    }

    public function bucketFor(Carbon $moment): string
    {
        return match ($this) {
            self::DAILY => $moment->format('Y-m-d'),
            self::WEEKLY => $moment->format('o-\WW'),
            self::MONTHLY => $moment->format('Y-m'),
        };
    }
}
