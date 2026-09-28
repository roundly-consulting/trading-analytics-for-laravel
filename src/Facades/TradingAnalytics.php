<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsManager;

/**
 * Pure calculation, so there is no `fake()`: the engine has no side effects to stub —
 * feed it the trades your test needs.
 *
 * @method static Analytics for(iterable<int, Trade|array<string, mixed>> $trades)
 * @method static Analytics calculate(iterable<int, Trade|array<string, mixed>> $trades, list<class-string<AnalyticsInterface>>|null $only = null)
 * @method static LazyCollection<int, Trade> trades(iterable<int, Trade|array<string, mixed>> $rows)
 * @method static list<class-string<AnalyticsInterface>> metrics()
 * @method static TradingAnalyticsManager using(string $analytics)
 * @method static class-string<Analytics> engine()
 *
 * @see TradingAnalyticsManager
 * @see Analytics
 */
final class TradingAnalytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TradingAnalyticsManager::class;
    }
}
