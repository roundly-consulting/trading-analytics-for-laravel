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
 * and its geometric mean. The `average` holds it as a mantissa in [1, 10) times
 * 10^`averageExponent`, so a long losing streak (0.95^500 ≈ 7 × 10^-12) never truncates to 0.
 */
class GrossCumulativeReturn implements SequentialAnalyticsInterface
{
    /** The scale each trade's return is divided at, like every other ratio the engine derives. */
    protected const int WORK_SCALE = 20;

    /** The decimals the mantissa of the running product behind the average keeps. */
    protected const int PRODUCT_SCALE = 30;

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
            $aggregate->average->set(1, scale: self::PRODUCT_SCALE);
            $aggregate->averageExponent = 0;
            $aggregate->highest->set(0, scale: 2);
            $aggregate->lowest->set(0, scale: 2);
        }

        $aggregate->count++;
        $aggregate->total->multiply($factor);
        static::compoundAverage($aggregate, $factor);

        $aggregate->trackExtremes(static::percentage($aggregate->total), $pair);
    }

    /**
     * Multiply one growth factor into the mantissa behind the average: exactly, then shifted
     * back into [1, 10) with the shift moved into the exponent, so the product keeps its
     * significant digits at any magnitude. A zero product (a −100 % trade) stays 0.
     */
    protected static function compoundAverage(NumericAggregates $aggregate, NumericValueAsString $factor): void
    {
        $exactScale = $aggregate->average->getScale() + $factor->getScale();
        $product = bcmul($aggregate->average->toRawString(), $factor->toRawString(), $exactScale);

        if (bccomp($product, '0', $exactScale) === 0) {
            $aggregate->average->set(0);

            return;
        }

        $exponent = BcMath::exponent($product);

        $aggregate->average->set(BcMath::shift($product, -$exponent));
        $aggregate->averageExponent += $exponent;
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

        $mantissa = $aggregate->average;
        $isNegative = $mantissa->isLessThan(0);

        // Geometric mean of the accumulated growth factors, computed entirely in
        // bcmath so the package's arbitrary-precision guarantee is not broken.
        $root = BcMath::nthRoot(
            value: BcMath::shift($mantissa->abs(immutable: true)->toRawString(), $aggregate->averageExponent),
            n: $aggregate->count,
        );

        // A negative product (a trade that lost more than 100 %) keeps its sign on the root:
        // −|product|^(1/n) is the real root for an odd count. An even count has no real root,
        // so the same signed root stands in for it and keeps the average below −100 %.
        $geometricMean = new NumericValueAsString(value: $root, scale: 20);

        if ($isNegative) {
            $geometricMean->multiply(-1);
        }

        return $geometricMean->subtract(1)->multiply(100)->round(2);
    }
}
