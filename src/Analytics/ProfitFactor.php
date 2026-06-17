<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

class ProfitFactor implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        //
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $analytics->profitFactor->total->set(static::calculateProfitFactor(
            profit: $analytics->unrealizedProfitAndLoss->grossProfits->total,
            loss: $analytics->unrealizedProfitAndLoss->grossLosses->total,
        ));

        $grossProfitsPerPair = $analytics->unrealizedProfitAndLoss->grossProfits->perPair;
        $grossLossesPerPair = $analytics->unrealizedProfitAndLoss->grossLosses->perPair;

        foreach ($grossProfitsPerPair as $pair => $profit) {
            $loss = $grossLossesPerPair[$pair] ?? null;

            $profitFactor = static::calculateProfitFactor(
                profit: $profit,
                loss: $loss,
            );

            $analytics->profitFactor->forPair($pair)->set($profitFactor);
        }

        $grossProfitsPerBaseCurrency = $analytics->unrealizedProfitAndLoss->grossProfits->perBaseCurrency;
        $grossLossesPerBaseCurrency = $analytics->unrealizedProfitAndLoss->grossLosses->perBaseCurrency;

        foreach ($grossProfitsPerBaseCurrency as $baseCurrency => $profit) {
            $loss = $grossLossesPerBaseCurrency[$baseCurrency] ?? null;

            $analytics->profitFactor->forBaseCurrency($baseCurrency)->set(static::calculateProfitFactor(
                profit: $profit,
                loss: $loss,
            ));
        }

        $grossProfitsPerQuoteCurrency = $analytics->unrealizedProfitAndLoss->grossProfits->perQuoteCurrency;
        $grossLossesPerQuoteCurrency = $analytics->unrealizedProfitAndLoss->grossLosses->perQuoteCurrency;

        foreach ($grossProfitsPerQuoteCurrency as $quoteCurrency => $profit) {
            $loss = $grossLossesPerQuoteCurrency[$quoteCurrency] ?? null;

            $analytics->profitFactor->forQuoteCurrency($quoteCurrency)->set(static::calculateProfitFactor(
                profit: $profit,
                loss: $loss,
            ));
        }
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
