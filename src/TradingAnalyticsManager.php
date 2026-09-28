<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidEngineException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnknownCalculatorException;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * The root of the {@see TradingAnalytics} facade, and the injectable entry point: it
 * normalises trade rows and builds the configured {@see Analytics} engine.
 *
 * A container singleton, so {@see using()} set in a service provider applies app-wide.
 *
 * @phpstan-type TradeRow array{base_currency: string, quote_currency: string, open_price: string|int|float, close_price: string|int|float, size: string|int|float, direction: string|Direction, open_time: string|Carbon, commission?: string|int|float|null, close_time?: string|Carbon|null}
 */
final class TradingAnalyticsManager
{
    /** @var class-string<Analytics> */
    private string $engine = Analytics::class;

    /**
     * Build the engine for a set of trades — `Trade` objects, rows in the
     * {@see Trade::fromArray()} shape, or a mix. Rows are mapped lazily, so a
     * `$query->lazy()` never sits in memory at once.
     *
     * @param  iterable<int, Trade|TradeRow>  $trades
     */
    public function for(iterable $trades): Analytics
    {
        return $this->engine::for($this->trades($trades));
    }

    /**
     * Build the engine and run it in one call, optionally restricted to the given
     * calculators (plus their dependencies).
     *
     * @param  iterable<int, Trade|TradeRow>  $trades
     * @param  list<class-string<AnalyticsInterface>>|null  $only
     *
     * @throws UnknownCalculatorException for a class that is not a calculator
     */
    public function calculate(iterable $trades, ?array $only = null): Analytics
    {
        $analytics = $this->for($trades);

        if ($only !== null) {
            $analytics->only($only);
        }

        return $analytics->calculate();
    }

    /**
     * Lazily map rows (arrays or trades) into trades.
     *
     * A row that is not a valid trade throws {@see InvalidTradeException} when iterated.
     *
     * @param  iterable<int, Trade|TradeRow>  $rows
     * @return LazyCollection<int, Trade>
     */
    public function trades(iterable $rows): LazyCollection
    {
        return Trade::collect($rows);
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
