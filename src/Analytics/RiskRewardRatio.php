<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Average winning trade divided by the average losing trade (the reward-to-risk
 * ratio). Built from the realized gross profit/loss aggregates in the single
 * pass, so it adds no extra iteration over the trades.
 */
class RiskRewardRatio implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $result = $analytics->riskRewardRatio;

        if ($result === null || $trade->isOpen()) {
            return;
        }

        $pnl = $trade->profitAndLoss();

        // A break-even trade is neither: counting it as a loss would dilute the average loss.
        if ($pnl->isPositiveNonZero()) {
            $result->winningTrades++;
        } elseif ($pnl->isLessThan(0)) {
            $result->losingTrades++;
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $result = $analytics->riskRewardRatio;

        if ($result === null) {
            return;
        }

        if ($result->winningTrades > 0) {
            $result->averageWin->set(
                $analytics->realizedProfitAndLoss->grossProfits->total->divide(
                    value: $result->winningTrades,
                    immutable: true,
                ),
            );
        }

        if ($result->losingTrades > 0) {
            $result->averageLoss->set(
                $analytics->realizedProfitAndLoss->grossLosses->total->abs(immutable: true)->divide(
                    value: $result->losingTrades,
                    immutable: true,
                ),
            );
        }

        if ($result->averageLoss->isZero()) {
            return;
        }

        $result->value->set(
            $result->averageWin->cloneWithScale(4)->divide(
                value: $result->averageLoss->cloneWithScale(4),
                immutable: true,
            ),
        );
    }
}
