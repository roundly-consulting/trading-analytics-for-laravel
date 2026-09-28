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
    /** The scale the ratio is divided at before it is truncated to its own. */
    protected const int WORK_SCALE = 20;

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

        if ($result->winningTrades === 0 || $result->losingTrades === 0) {
            return;
        }

        // (profit / wins) / (loss / losses), taken from the exact totals at the work scale and
        // truncated only as the result: a 4-decimal average loss below 0.0001 was zero (a crash
        // past the non-zero guard), and any other skewed the ratio.
        $profit = $analytics->realizedProfitAndLoss->grossProfits->total->cloneWithScale(self::WORK_SCALE);
        $loss = $analytics->realizedProfitAndLoss->grossLosses->total->cloneWithScale(self::WORK_SCALE)->abs();

        $result->value->set(
            $profit->multiply($result->losingTrades)->divide($loss->multiply($result->winningTrades)),
        );
    }
}
