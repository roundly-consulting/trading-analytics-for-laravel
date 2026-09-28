<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\SequentialAnalyticsInterface;

/**
 * Largest peak-to-trough drop of the realized equity curve, tracked with a
 * running peak in the single pass (no equity series stored).
 */
class MaxDrawdown implements SequentialAnalyticsInterface
{
    /** The scale the percentage is divided at before it is truncated to its own. */
    protected const int WORK_SCALE = 20;

    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $result = $analytics->maxDrawdown;

        if ($result === null || $trade->isOpen()) {
            return;
        }

        $result->equity->add($trade->profitAndLoss(subtractCommissions: true));

        if ($result->equity->isGreaterThan($result->peak)) {
            $result->peak->set($result->equity);

            return;
        }

        $drawdown = $result->peak->subtract(value: $result->equity, immutable: true);

        if ($drawdown->isGreaterThan($result->value)) {
            $result->value->set($drawdown);

            // Full precision until the result: a peak below 0.0001 truncated to 4 decimals
            // first was zero (a crash past the non-zero guard), and any other was skewed.
            if ($result->peak->isPositiveNonZero()) {
                $result->percentage->set(
                    $drawdown->cloneWithScale(self::WORK_SCALE)
                        ->divide(value: $result->peak, immutable: true)
                        ->multiply(value: 100),
                );
            }
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        //
    }
}
