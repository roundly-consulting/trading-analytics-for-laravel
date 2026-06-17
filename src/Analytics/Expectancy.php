<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Expected value of an average trade: (winRate * avgWin) - (lossRate * avgLoss).
 * Derived from the realized gross profit/loss aggregates already collected in
 * the single pass, so it adds no extra iteration over the trades.
 */
class Expectancy implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $result = $analytics->expectancy;

        if ($result === null || $trade->isOpen()) {
            return;
        }

        if ($trade->profitAndLoss()->isPositiveNonZero()) {
            $result->winningTrades++;
        } else {
            $result->losingTrades++;
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $result = $analytics->expectancy;

        if ($result === null) {
            return;
        }

        $total = $result->winningTrades + $result->losingTrades;

        if ($total === 0) {
            return;
        }

        $grossProfits = $analytics->realizedProfitAndLoss->grossProfits->total;
        $grossLosses = $analytics->realizedProfitAndLoss->grossLosses->total->abs(immutable: true);

        if ($result->winningTrades > 0) {
            $result->averageWin->set($grossProfits->divide(value: $result->winningTrades, immutable: true));
        }

        if ($result->losingTrades > 0) {
            $result->averageLoss->set($grossLosses->divide(value: $result->losingTrades, immutable: true));
        }

        $result->winRate->set((new NumericValueAsString(value: $result->winningTrades, scale: 10))->divide(value: $total, immutable: true));
        $result->lossRate->set((new NumericValueAsString(value: $result->losingTrades, scale: 10))->divide(value: $total, immutable: true));

        $expectedWin = $result->winRate->cloneWithScale(10)->multiply(value: $result->averageWin, immutable: true);
        $expectedLoss = $result->lossRate->cloneWithScale(10)->multiply(value: $result->averageLoss, immutable: true);

        $result->value->set($expectedWin->subtract(value: $expectedLoss, immutable: true));
    }
}
