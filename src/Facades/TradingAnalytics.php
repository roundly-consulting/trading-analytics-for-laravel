<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Facades;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
 * A trade source is an iterable of trades or rows (arrays, `stdClass` rows, Eloquent models,
 * `Arrayable`s, plain objects), or an ORDERED query — streamed in `$chunk`-row pages.
 *
 * @method static Analytics for(iterable<Trade|array<array-key, mixed>|object>|QueryBuilder|EloquentBuilder<*>|Relation<*, *, *> $trades, int $chunk = 1000)
 * @method static Analytics calculate(iterable<Trade|array<array-key, mixed>|object>|QueryBuilder|EloquentBuilder<*>|Relation<*, *, *> $trades, list<class-string<AnalyticsInterface>>|null $only = null, int $chunk = 1000)
 * @method static LazyCollection<int, Trade> trades(iterable<Trade|array<array-key, mixed>|object>|QueryBuilder|EloquentBuilder<*>|Relation<*, *, *> $rows, int $chunk = 1000)
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
