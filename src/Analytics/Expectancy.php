<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Expected value of an average closed trade: (winRate × avgWin) − (lossRate × avgLoss).
 *
 * A break-even trade is neither a win nor a loss — it only dilutes both rates — so the
 * expression reduces to (gross profit − gross loss) / closed trades, which is how the value is
 * computed: straight from the realized gross totals, with no rounded rate in between.
 */
class Expectancy implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $result = $analytics->expectancy;

        if ($result === null || $trade->isOpen()) {
            return;
        }

        $pnl = $trade->profitAndLoss();

        match (true) {
            $pnl->isPositiveNonZero() => $result->winningTrades++,
            $pnl->isLessThan(0) => $result->losingTrades++,
            default => $result->breakEvenTrades++,
        };
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $result = $analytics->expectancy;

        if ($result === null) {
            return;
        }

        $total = $result->winningTrades + $result->losingTrades + $result->breakEvenTrades;

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

        $result->winRate->set(static::rate($result->winningTrades, $total));
        $result->lossRate->set(static::rate($result->losingTrades, $total));

        $result->value->set(
            $grossProfits->subtract(value: $grossLosses, immutable: true)->divide(value: $total),
        );
    }

    protected static function rate(int $trades, int $total): NumericValueAsString
    {
        return (new NumericValueAsString(value: $trades, scale: 10))->divide(value: $total);
    }
}
