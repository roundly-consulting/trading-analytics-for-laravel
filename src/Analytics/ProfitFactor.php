<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Realized gross profit divided by realized gross loss — globally and per pair / base / quote
 * currency. Undefined (no realized loss) reads 0.
 */
class ProfitFactor implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        //
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $profits = $analytics->realizedProfitAndLoss->grossProfits;
        $losses = $analytics->realizedProfitAndLoss->grossLosses;

        $analytics->profitFactor->total->set(static::calculateProfitFactor($profits->total, $losses->total));

        foreach (static::keys($profits->perPair, $losses->perPair) as $pair) {
            $analytics->profitFactor->forPair($pair)->set(
                static::calculateProfitFactor($profits->perPair[$pair] ?? null, $losses->perPair[$pair] ?? null),
            );
        }

        foreach (static::keys($profits->perBaseCurrency, $losses->perBaseCurrency) as $baseCurrency) {
            $analytics->profitFactor->forBaseCurrency($baseCurrency)->set(
                static::calculateProfitFactor($profits->perBaseCurrency[$baseCurrency] ?? null, $losses->perBaseCurrency[$baseCurrency] ?? null),
            );
        }

        foreach (static::keys($profits->perQuoteCurrency, $losses->perQuoteCurrency) as $quoteCurrency) {
            $analytics->profitFactor->forQuoteCurrency($quoteCurrency)->set(
                static::calculateProfitFactor($profits->perQuoteCurrency[$quoteCurrency] ?? null, $losses->perQuoteCurrency[$quoteCurrency] ?? null),
            );
        }
    }

    /**
     * Every key with a realized profit or a realized loss, so a loss-only pair reads 0 rather
     * than going missing.
     *
     * @param  array<string, NumericValueAsString>  $profits
     * @param  array<string, NumericValueAsString>  $losses
     * @return list<string>
     */
    protected static function keys(array $profits, array $losses): array
    {
        return array_map(strval(...), array_keys($profits + $losses));
    }

    protected static function calculateProfitFactor(
        ?NumericValueAsString $profit,
        ?NumericValueAsString $loss
    ): NumericValueAsString {
        $default = new NumericValueAsString(scale: 2);

        if ($profit === null || $loss === null) {
            return $default;
        }

        $losses = $loss->abs(immutable: true);

        if ($losses->isZero()) {
            return $default;
        }

        return $profit->divide(
            value: $losses,
            immutable: true
        );
    }
}
