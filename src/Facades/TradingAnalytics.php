<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\AnalyticsFactory;

/**
 * @method static Analytics make(LazyCollection<int, \RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade> $trades)
 * @method static Analytics for(LazyCollection<int, \RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade> $trades)
 *
 * @see AnalyticsFactory
 */
final class TradingAnalytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'trading-analytics';
    }
}
