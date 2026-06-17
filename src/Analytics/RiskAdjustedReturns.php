<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\MultiPassAnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\Support\BcMath;

/**
 * Sharpe and Sortino ratios. Unlike the single-pass aggregate calculators these
 * need the full per-trade return series to compute variance, so they live on the
 * package's separated multi-pass path: the series is gathered during the pass
 * and the variance / deviation work happens once over that stored series in
 * {@see calculateAfterTrades()}.
 */
class RiskAdjustedReturns implements MultiPassAnalyticsInterface
{
    protected const WORK_SCALE = 20;

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

        if ($result === null || $result->returns === []) {
            return;
        }

        $count = count($result->returns);

        $mean = static::mean($result->returns, $count);
        $result->meanReturn->set($mean);

        $excess = $mean->subtract(value: $result->riskFreeRate, immutable: true);

        $standardDeviation = static::standardDeviation($result->returns, $mean, $count);
        $result->standardDeviation->set($standardDeviation);

        $downsideDeviation = static::downsideDeviation($result->returns, $result->riskFreeRate, $count);
        $result->downsideDeviation->set($downsideDeviation);

        if ($standardDeviation->isPositiveNonZero()) {
            $result->sharpeRatio->set(
                $excess->cloneWithScale(4)->divide(value: $standardDeviation->cloneWithScale(4), immutable: true),
            );
        }

        if ($downsideDeviation->isPositiveNonZero()) {
            $result->sortinoRatio->set(
                $excess->cloneWithScale(4)->divide(value: $downsideDeviation->cloneWithScale(4), immutable: true),
            );
        }
    }

    /**
     * @param  list<NumericValueAsString>  $returns
     */
    protected static function mean(array $returns, int $count): NumericValueAsString
    {
        $sum = new NumericValueAsString(scale: self::WORK_SCALE);

        foreach ($returns as $return) {
            $sum->add($return);
        }

        return $sum->divide(value: $count, immutable: true);
    }

    /**
     * Population standard deviation, computed in bcmath.
     *
     * @param  list<NumericValueAsString>  $returns
     */
    protected static function standardDeviation(array $returns, NumericValueAsString $mean, int $count): NumericValueAsString
    {
        $sumSquares = new NumericValueAsString(scale: self::WORK_SCALE);

        foreach ($returns as $return) {
            $deviation = $return->subtract(value: $mean, immutable: true);
            $sumSquares->add($deviation->multiply(value: $deviation, immutable: true));
        }

        $variance = $sumSquares->divide(value: $count, immutable: true);

        return new NumericValueAsString(
            value: BcMath::sqrt($variance->toRawString(), self::WORK_SCALE),
            scale: self::WORK_SCALE,
        );
    }

    /**
     * Downside deviation against the risk-free rate (Sortino denominator).
     *
     * @param  list<NumericValueAsString>  $returns
     */
    protected static function downsideDeviation(array $returns, NumericValueAsString $riskFreeRate, int $count): NumericValueAsString
    {
        $sumSquares = new NumericValueAsString(scale: self::WORK_SCALE);

        foreach ($returns as $return) {
            $shortfall = $return->subtract(value: $riskFreeRate, immutable: true);

            if (! $shortfall->isLessThan(0)) {
                continue;
            }

            $sumSquares->add($shortfall->multiply(value: $shortfall, immutable: true));
        }

        $variance = $sumSquares->divide(value: $count, immutable: true);

        return new NumericValueAsString(
            value: BcMath::sqrt($variance->toRawString(), self::WORK_SCALE),
            scale: self::WORK_SCALE,
        );
    }
}
