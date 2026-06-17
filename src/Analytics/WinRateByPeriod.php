<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Win rate bucketed by calendar period (daily / weekly / monthly), keyed on each
 * trade's open time. Buckets are filled in the single pass.
 */
class WinRateByPeriod implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $result = $analytics->winRateByPeriod;

        if ($result === null || $trade->isOpen()) {
            return;
        }

        $result->record(
            bucket: $result->period->bucketFor($trade->openTime),
            isWin: $trade->profitAndLoss()->isPositiveNonZero(),
        );
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $result = $analytics->winRateByPeriod;

        if ($result === null) {
            return;
        }

        foreach ($result->totals as $bucket => $total) {
            if ($total === 0) {
                continue;
            }

            $wins = $result->wins[$bucket] ?? 0;

            $result->rates[$bucket] = (new NumericValueAsString(value: $wins, scale: 4))
                ->divide(value: $total, immutable: true);
        }
    }
}
