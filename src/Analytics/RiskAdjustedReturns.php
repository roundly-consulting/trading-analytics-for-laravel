<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\RiskAdjustedReturns as RiskAdjustedReturnsResult;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\MultiPassAnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\Support\BcMath;

/**
 * Sharpe and Sortino ratios in constant memory. The pass folds each realized net return into
 * running sums (count, Σr, Σr², Σ shortfall²); {@see calculateAfterTrades()} derives the mean,
 * the population standard deviation and the downside deviation from those sums alone, so the
 * per-trade return series is never held.
 */
class RiskAdjustedReturns implements MultiPassAnalyticsInterface
{
    protected const WORK_SCALE = RiskAdjustedReturnsResult::WORK_SCALE;

    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $result = $analytics->riskAdjustedReturns;

        if ($result === null || $trade->isOpen()) {
            return;
        }

        $result->recordReturn(
            $trade->roi(subtractCommissions: true, asPercentage: false)->cloneWithScale(self::WORK_SCALE),
        );
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $result = $analytics->riskAdjustedReturns;

        if ($result === null || $result->sampleSize === 0) {
            return;
        }

        $count = $result->sampleSize;

        $mean = $result->sumOfReturns->divide(value: $count, immutable: true);
        $result->meanReturn->set($mean);

        $excess = $mean->subtract(value: $result->riskFreeRate, immutable: true);

        $standardDeviation = static::standardDeviation($result, $mean, $count);
        $result->standardDeviation->set($standardDeviation);

        $downsideDeviation = static::downsideDeviation($result, $count);
        $result->downsideDeviation->set($downsideDeviation);

        // Divide at the work scale and truncate only the result: a deviation below 0.0001
        // truncated to 4 decimals first was zero (a crash past the non-zero guard) or skewed.
        if ($standardDeviation->isPositiveNonZero()) {
            $result->sharpeRatio->set($excess->divide(value: $standardDeviation, immutable: true));
        }

        if ($downsideDeviation->isPositiveNonZero()) {
            $result->sortinoRatio->set($excess->divide(value: $downsideDeviation, immutable: true));
        }
    }

    /**
     * Population standard deviation from the running sums.
     *
     * Σ(r − m)² = Σr² − 2m·Σr + n·m², evaluated exactly at the square scale against the same
     * truncated mean `m` the result reports, then divided by n and rooted in bcmath.
     */
    protected static function standardDeviation(RiskAdjustedReturnsResult $result, NumericValueAsString $mean, int $count): NumericValueAsString
    {
        $mean = $mean->cloneWithScale(RiskAdjustedReturnsResult::SQUARE_SCALE);

        $sumOfSquaredDeviations = $result->sumOfSquaredReturns
            ->subtract(value: $mean->multiply(value: $result->sumOfReturns, immutable: true)->multiply(value: 2), immutable: true)
            ->add(value: $mean->multiply(value: $mean, immutable: true)->multiply(value: $count));

        $variance = $sumOfSquaredDeviations
            ->divide(value: $count)
            ->cloneWithScale(self::WORK_SCALE);

        return new NumericValueAsString(
            value: BcMath::sqrt($variance->toRawString(), self::WORK_SCALE),
            scale: self::WORK_SCALE,
        );
    }

    /**
     * Downside deviation against the risk-free rate (the Sortino denominator).
     */
    protected static function downsideDeviation(RiskAdjustedReturnsResult $result, int $count): NumericValueAsString
    {
        $variance = $result->sumOfSquaredShortfalls->divide(value: $count, immutable: true);

        return new NumericValueAsString(
            value: BcMath::sqrt($variance->toRawString(), self::WORK_SCALE),
            scale: self::WORK_SCALE,
        );
    }
}
