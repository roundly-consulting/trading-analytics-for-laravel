<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidChunkSizeException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidEngineException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnknownCalculatorException;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnorderedTradeSourceException;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * The root of the {@see TradingAnalytics} facade, and the injectable entry point: it
 * normalises trade sources and builds the configured {@see Analytics} engine.
 *
 * A trade source is any iterable of trades or rows ({@see Trade::fromRow()} reads arrays,
 * `stdClass` rows, Eloquent models, `Arrayable`s and plain objects), or a query — a query
 * builder, an Eloquent builder or a relation — which is streamed with `lazy($chunk)`, one
 * page of `$chunk` rows in memory at a time. The metrics depend on trade order, so a query
 * must carry an ORDER BY; an unordered one throws {@see UnorderedTradeSourceException}.
 *
 * A container singleton, so {@see using()} set in a service provider applies app-wide.
 *
 * @phpstan-import-type TradeRow from Trade
 *
 * @phpstan-type TradeSource iterable<Trade|TradeRow|array<array-key, mixed>|object>|QueryBuilder|EloquentBuilder<*>|Relation<*, *, *>
 */
final class TradingAnalyticsManager
{
    /** Rows per page when a query is streamed. */
    public const int DEFAULT_CHUNK = 1000;

    /** @var class-string<Analytics> */
    private string $engine = Analytics::class;

    /**
     * Build the engine for a trade source, ready to configure. Nothing is read until the
     * engine runs.
     *
     * @param  TradeSource  $trades
     * @param  int  $chunk  rows per page when `$trades` is a query
     *
     * @throws UnorderedTradeSourceException for a query without an ORDER BY
     * @throws InvalidChunkSizeException for a chunk size below 1
     */
    public function for(iterable|QueryBuilder|EloquentBuilder|Relation $trades, int $chunk = self::DEFAULT_CHUNK): Analytics
    {
        return $this->engine::for($this->trades($trades, $chunk));
    }

    /**
     * Build the engine and run it in one call, optionally restricted to the given
     * calculators (plus their dependencies).
     *
     * @param  TradeSource  $trades
     * @param  list<class-string<AnalyticsInterface>>|null  $only
     * @param  int  $chunk  rows per page when `$trades` is a query
     *
     * @throws UnknownCalculatorException for a class that is not a calculator
     * @throws UnorderedTradeSourceException for a query without an ORDER BY
     * @throws InvalidChunkSizeException for a chunk size below 1
     */
    public function calculate(iterable|QueryBuilder|EloquentBuilder|Relation $trades, ?array $only = null, int $chunk = self::DEFAULT_CHUNK): Analytics
    {
        $analytics = $this->for($trades, $chunk);

        if ($only !== null) {
            $analytics->only($only);
        }

        return $analytics->calculate();
    }

    /**
     * Lazily map a trade source into trades. A query is checked for an ORDER BY here, before
     * anything runs, and then paged with `lazy($chunk)` on a clone, so the caller's builder
     * is left as it was.
     *
     * A row that is not a valid trade throws {@see InvalidTradeException} when iterated.
     *
     * @param  TradeSource  $rows
     * @param  int  $chunk  rows per page when `$rows` is a query
     * @return LazyCollection<int, Trade>
     *
     * @throws UnorderedTradeSourceException for a query without an ORDER BY
     * @throws InvalidChunkSizeException for a chunk size below 1
     */
    public function trades(iterable|QueryBuilder|EloquentBuilder|Relation $rows, int $chunk = self::DEFAULT_CHUNK): LazyCollection
    {
        if ($chunk < 1) {
            throw InvalidChunkSizeException::tooSmall($chunk);
        }

        if (is_iterable($rows)) {
            return Trade::collect($rows);
        }

        $base = match (true) {
            $rows instanceof QueryBuilder => $rows,
            $rows instanceof EloquentBuilder => $rows->getQuery(),
            default => $rows->getBaseQuery(),
        };

        if (empty($base->orders) && empty($base->unionOrders)) {
            throw UnorderedTradeSourceException::for($rows);
        }

        return Trade::collect((clone $rows)->lazy($chunk));
    }

    /**
     * The calculator class-strings the configured engine runs, in canonical order — the
     * values `calculate(only: …)`, `only()` and `except()` accept.
     *
     * @return list<class-string<AnalyticsInterface>>
     */
    public function metrics(): array
    {
        return $this->engine::for(LazyCollection::empty())->metrics();
    }

    /**
     * Build every engine from a subclass of {@see Analytics} (custom calculators or hooks).
     * The manager is a singleton, so call it once — e.g. in a service provider's `boot()`.
     *
     * @param  string  $analytics  the class-string of Analytics or a subclass — validated here,
     *                             because hosts pass it from their own code or config
     *
     * @throws InvalidEngineException for a class that is not an Analytics engine
     */
    public function using(string $analytics): self
    {
        if (! is_a($analytics, Analytics::class, true)) {
            throw InvalidEngineException::notAnAnalyticsEngine($analytics);
        }

        $this->engine = $analytics;

        return $this;
    }

    /**
     * The engine class {@see for()} builds.
     *
     * @return class-string<Analytics>
     */
    public function engine(): string
    {
        return $this->engine;
    }
}
