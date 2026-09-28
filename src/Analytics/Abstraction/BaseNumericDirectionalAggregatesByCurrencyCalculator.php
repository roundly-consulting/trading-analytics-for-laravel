<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics\Abstraction;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Total / average / highest / lowest of one per-trade value, globally and per pair, base
 * currency and quote currency, each split into total, buy and sell.
 *
 * Every aggregate counts the trades that fed it and averages over exactly those — a realized
 * figure over the closed trades, an unrealized one over the open trades — so a calculator is
 * self-contained: it depends on no other calculator.
 */
abstract class BaseNumericDirectionalAggregatesByCurrencyCalculator implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        if (! static::shouldCalculatePerTrade($analytics, $trade)) {
            return;
        }

        $dto = static::dto($analytics);
        $value = static::value($trade);
        $pair = $trade->pair();

        foreach ([$dto->global, $dto->forPair($pair), $dto->forBaseCurrency($trade->baseCurrency), $dto->forQuoteCurrency($trade->quoteCurrency)] as $aggregates) {
            $aggregates->total->record($value, $pair);
            ($trade->direction->isBuy() ? $aggregates->buy : $aggregates->sell)->record($value, $pair);
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        foreach (static::everyAggregates(static::dto($analytics)) as $aggregates) {
            static::calculateAverage($aggregates->total);
            static::calculateAverage($aggregates->buy);
            static::calculateAverage($aggregates->sell);
        }

        static::after($analytics);
    }

    protected static function after(Analytics $analytics): void
    {
        //
    }

    protected static function shouldCalculatePerTrade(Analytics $analytics, Trade $trade): bool
    {
        return true;
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->volume;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->size;
    }

    protected static function calculateAverage(NumericAggregates $aggregate): void
    {
        if ($aggregate->count > 0) {
            $aggregate->average = $aggregate->total->divide(value: $aggregate->count, immutable: true);
        }
    }

    /**
     * The global aggregates followed by every per-pair, per-base and per-quote one. A list,
     * because a currency that is the base of one pair and the quote of another keys both maps.
     *
     * @return list<NumericDirectionalAggregates>
     */
    protected static function everyAggregates(NumericDirectionalAggregatesByCurrency $dto): array
    {
        return [
            $dto->global,
            ...array_values($dto->perPair),
            ...array_values($dto->perBaseCurrency),
            ...array_values($dto->perQuoteCurrency),
        ];
    }
}
