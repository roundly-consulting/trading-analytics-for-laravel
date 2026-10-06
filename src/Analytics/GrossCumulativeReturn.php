<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\SequentialAnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\Support\BcMath;

/**
 * Cumulative return of compounding every trade's return (open trades at their close price),
 * in percent, with the geometric mean per trade as the average and the highest / lowest
 * running cumulative return along the way.
 *
 * During the pass an aggregate's `total` and `average` both carry the running product of the
 * growth factors (1 + return); the after-trades hook turns them into the cumulative return
 * and its geometric mean.
 */
class GrossCumulativeReturn implements SequentialAnalyticsInterface
{
    /** The scale each trade's return is divided at, like every other ratio the engine derives. */
    protected const int WORK_SCALE = 20;

    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $dto = static::dto($analytics);
        $factor = static::getReturnFromTrade($trade)->add(value: 1, immutable: true);

        foreach ([$dto->global, $dto->forPair($trade->pair()), $dto->forBaseCurrency($trade->baseCurrency), $dto->forQuoteCurrency($trade->quoteCurrency)] as $aggregates) {
            static::compound($aggregates->total, $factor, $trade->pair());
            static::compound($trade->direction->isBuy() ? $aggregates->buy : $aggregates->sell, $factor, $trade->pair());
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $dto = static::dto($analytics);

        foreach ([$dto->global, ...array_values($dto->perPair), ...array_values($dto->perBaseCurrency), ...array_values($dto->perQuoteCurrency)] as $aggregates) {
            static::calculateAfterTradesCumulativeReturns($aggregates);
        }
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->cumulativeReturn->gross;
    }

    protected static function getReturnFromTrade(Trade $trade): NumericValueAsString
    {
        return $trade->roi(asPercentage: false, scale: self::WORK_SCALE);
    }

    /**
     * Multiply one growth factor into the running products. The first trade starts them at 1
     * — decided by the trade count, not by a zero product, which is a real result (a −100 %
     * trade) that later trades must not reset.
     */
    protected static function compound(NumericAggregates $aggregate, NumericValueAsString $factor, string $pair): void
    {
        if ($aggregate->count === 0) {
            $aggregate->total->set(1);
            $aggregate->average->set(1);
            $aggregate->highest->set(0, scale: 2);
            $aggregate->lowest->set(0, scale: 2);
        }

        $aggregate->count++;
        $aggregate->total->multiply($factor);
        $aggregate->average->multiply($factor);

        $aggregate->trackExtremes(static::percentage($aggregate->total), $pair);
    }

    protected static function calculateAfterTradesCumulativeReturns(NumericDirectionalAggregates $dto): void
    {
        foreach ([$dto->total, $dto->buy, $dto->sell] as $aggregate) {
            $cumulativeReturn = $aggregate->count > 0 ? static::percentage($aggregate->total) : new NumericValueAsString(scale: 2);

            $aggregate->total->set(value: $cumulativeReturn, scale: 2);
            $aggregate->average->set(value: static::calculateAverageCumulativeReturn($aggregate), scale: 2);
        }
    }

    /** A running product of growth factors as a percentage return, to 2 decimals. */
    protected static function percentage(NumericValueAsString $product): NumericValueAsString
    {
        return $product->subtract(value: 1, immutable: true)
            ->multiply(100)
            ->round(2);
    }

    protected static function calculateAverageCumulativeReturn(NumericAggregates $aggregate): NumericValueAsString
    {
        if ($aggregate->count === 0) {
            return new NumericValueAsString(scale: 2);
        }

        $product = $aggregate->average;
        $isNegative = $product->isLessThan(0);

        // Geometric mean of the accumulated growth factors, computed entirely in
        // bcmath so the package's arbitrary-precision guarantee is not broken.
        $root = BcMath::nthRoot(
            value: $product->abs(immutable: true)->toRawString(),
            n: $aggregate->count,
        );

        $geometricMean = new NumericValueAsString(value: $root, scale: 20);
        $geometricMean->subtract(1);

        return $geometricMean->multiply($isNegative ? -100 : 100)->round(2);
    }
}
