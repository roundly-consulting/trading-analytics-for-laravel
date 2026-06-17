<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Largest peak-to-trough drop of the realized equity curve, tracked with a
 * running peak in the single pass (no equity series stored).
 */
class MaxDrawdown implements AnalyticsInterface
{
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

            if ($result->peak->isPositiveNonZero()) {
                $result->percentage->set(
                    $drawdown->cloneWithScale(4)
                        ->divide(value: $result->peak->cloneWithScale(4), immutable: true)
                        ->multiply(value: 100, immutable: true),
                );
            }
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        //
    }
}
