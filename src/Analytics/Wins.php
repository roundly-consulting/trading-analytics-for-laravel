<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByDirections;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * Winning trades and the win ratio, over closed trades only: an open trade has no outcome
 * yet. A break-even trade is a closed trade that did not win.
 */
class Wins implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        if ($trade->isOpen()) {
            return;
        }

        static::increment($analytics->wins->closed, $trade);

        if ($trade->profitAndLoss()->isPositiveNonZero()) {
            static::increment($analytics->wins, $trade);
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $wins = $analytics->wins;
        $ratio = $wins->winRatio;

        static::calculateRatios($wins->global, $wins->closed->global, $ratio->global);

        foreach ($wins->closed->perPair as $pair => $closed) {
            static::calculateRatios($wins->perPair[$pair] ?? null, $closed, $ratio->forPair($pair));
        }

        foreach ($wins->closed->perBaseCurrency as $baseCurrency => $closed) {
            static::calculateRatios($wins->perBaseCurrency[$baseCurrency] ?? null, $closed, $ratio->forBaseCurrency($baseCurrency));
        }

        foreach ($wins->closed->perQuoteCurrency as $quoteCurrency => $closed) {
            static::calculateRatios($wins->perQuoteCurrency[$quoteCurrency] ?? null, $closed, $ratio->forQuoteCurrency($quoteCurrency));
        }
    }

    protected static function increment(NumericDirectionalByCurrency $dto, Trade $trade): void
    {
        foreach ([$dto->global, $dto->forPair($trade->pair()), $dto->forBaseCurrency($trade->baseCurrency), $dto->forQuoteCurrency($trade->quoteCurrency)] as $counts) {
            $counts->total->add(1);
            ($trade->direction->isBuy() ? $counts->buy : $counts->sell)->add(1);
        }
    }

    protected static function calculateRatios(?NumericByDirections $wins, NumericByDirections $closed, NumericByDirections $ratio): void
    {
        $ratio->total = static::calculateRatio($wins?->total, $closed->total);
        $ratio->buy = static::calculateRatio($wins?->buy, $closed->buy);
        $ratio->sell = static::calculateRatio($wins?->sell, $closed->sell);
    }

    protected static function calculateRatio(
        ?NumericValueAsString $value,
        NumericValueAsString $total
    ): NumericValueAsString {
        if ($value === null || $total->isZero()) {
            return new NumericValueAsString(scale: 2);
        }

        return $value->cloneWithScale(2)
            ->divide(
                value: $total->cloneWithScale(2),
                immutable: true,
            );
    }
}
