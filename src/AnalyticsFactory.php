<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

/**
 * Resolvable factory behind the optional {@see TradingAnalytics}
 * facade, so consumers can build an {@see Analytics} instance through the container.
 */
final class AnalyticsFactory
{
    /** @param LazyCollection<int, Trade> $trades */
    public function make(LazyCollection $trades): Analytics
    {
        return Analytics::make($trades);
    }

    /** @param LazyCollection<int, Trade> $trades */
    public function for(LazyCollection $trades): Analytics
    {
        return Analytics::for($trades);
    }
}
