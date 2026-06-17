<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByDirections;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

class Wins implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        if ($trade->profitAndLoss()->isLessThanOrEqualTo(0)) {
            return;
        }

        // Global, per pair, per base currency and per quote currency total number of winning trades
        $analytics->wins->global->total->add(1);
        $analytics->wins->forPair($trade->pair())->total->add(1);
        $analytics->wins->forBaseCurrency($trade->baseCurrency)->total->add(1);
        $analytics->wins->forQuoteCurrency($trade->quoteCurrency)->total->add(1);

        if ($trade->direction->isBuy()) {
            // Global, per pair, per base currency and per quote currency total number of winning trades by direction Buy
            $analytics->wins->global->buy->add(1);
            $analytics->wins->forPair($trade->pair())->buy->add(1);
            $analytics->wins->forBaseCurrency($trade->baseCurrency)->buy->add(1);
            $analytics->wins->forQuoteCurrency($trade->quoteCurrency)->buy->add(1);
        } else {
            // Global, per pair, per base currency and per quote currency total number of winning trades by direction Sell
            $analytics->wins->global->sell->add(1);
            $analytics->wins->forPair($trade->pair())->sell->add(1);
            $analytics->wins->forBaseCurrency($trade->baseCurrency)->sell->add(1);
            $analytics->wins->forQuoteCurrency($trade->quoteCurrency)->sell->add(1);
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        static::calculateGlobalWinRatio($analytics);
        static::calculatePairWinRatio($analytics);
        static::calculateBaseCurrencyWinRatio($analytics);
        static::calculateQuoteCurrencyWinRatio($analytics);
    }

    protected static function calculateGlobalWinRatio(Analytics $analytics): void
    {
        $analytics->wins->winRatio->global->total = static::calculateRatio(
            $analytics->wins->global->total,
            $analytics->counts->global->total,
        );

        $analytics->wins->winRatio->global->buy = static::calculateRatio(
            $analytics->wins->global->buy,
            $analytics->counts->global->buy,
        );

        $analytics->wins->winRatio->global->sell = static::calculateRatio(
            $analytics->wins->global->sell,
            $analytics->counts->global->sell,
        );
    }

    protected static function calculatePairWinRatio(Analytics $analytics): void
    {
        /** @var NumericByDirections $pairWins */
        foreach ($analytics->wins->perPair as $pair => $pairWins) {
            $analytics->wins->winRatio->forPair($pair)->total = static::calculateRatio(
                $pairWins->total,
                $analytics->counts->forPair($pair)->total,
            );

            $analytics->wins->winRatio->forPair($pair)->buy = static::calculateRatio(
                $pairWins->buy,
                $analytics->counts->forPair($pair)->buy,
            );

            $analytics->wins->winRatio->forPair($pair)->sell = static::calculateRatio(
                $pairWins->sell,
                $analytics->counts->forPair($pair)->sell,
            );
        }
    }

    protected static function calculateBaseCurrencyWinRatio(Analytics $analytics): void
    {
        /** @var NumericByDirections $pairWins */
        foreach ($analytics->wins->perBaseCurrency as $baseCurrency => $pairWins) {
            $analytics->wins->winRatio->forBaseCurrency($baseCurrency)->total = static::calculateRatio(
                $pairWins->total,
                $analytics->counts->forBaseCurrency($baseCurrency)->total,
            );

            $analytics->wins->winRatio->forBaseCurrency($baseCurrency)->buy = static::calculateRatio(
                $pairWins->buy,
                $analytics->counts->forBaseCurrency($baseCurrency)->buy,
            );

            $analytics->wins->winRatio->forBaseCurrency($baseCurrency)->sell = static::calculateRatio(
                $pairWins->sell,
                $analytics->counts->forBaseCurrency($baseCurrency)->sell,
            );
        }
    }

    protected static function calculateQuoteCurrencyWinRatio(Analytics $analytics): void
    {
        /** @var NumericByDirections $pairWins */
        foreach ($analytics->wins->perQuoteCurrency as $quoteCurrency => $pairWins) {
            $analytics->wins->winRatio->forQuoteCurrency($quoteCurrency)->total = static::calculateRatio(
                $pairWins->total,
                $analytics->counts->forQuoteCurrency($quoteCurrency)->total,
            );

            $analytics->wins->winRatio->forQuoteCurrency($quoteCurrency)->buy = static::calculateRatio(
                $pairWins->buy,
                $analytics->counts->forQuoteCurrency($quoteCurrency)->buy,
            );

            $analytics->wins->winRatio->forQuoteCurrency($quoteCurrency)->sell = static::calculateRatio(
                $pairWins->sell,
                $analytics->counts->forQuoteCurrency($quoteCurrency)->sell,
            );
        }
    }

    protected static function calculateRatio(
        NumericValueAsString $value,
        NumericValueAsString $total
    ): NumericValueAsString {
        if ($total->isZero()) {
            return new NumericValueAsString(scale: 2);
        }

        return $value->cloneWithScale(2)
            ->divide(
                value: $total->cloneWithScale(2),
                immutable: true,
            );
    }
}
