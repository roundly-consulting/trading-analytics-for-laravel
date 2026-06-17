<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByDirections;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

class GrossCumulativeReturn implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        static::calculatePerTradeCumulativeReturns($trade, static::dto($analytics)->global);
        static::calculatePerTradeCumulativeReturns($trade, static::dto($analytics)->forPair($trade->pair()));
        static::calculatePerTradeCumulativeReturns($trade, static::dto($analytics)->forBaseCurrency($trade->baseCurrency));
        static::calculatePerTradeCumulativeReturns($trade, static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency));
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        self::calculateAfterTradesCumulativeReturns(static::dto($analytics)->global, $analytics->counts->global);

        foreach (static::dto($analytics)->perPair as $pair => $dto) {
            self::calculateAfterTradesCumulativeReturns($dto, $analytics->counts->forPair($pair));
        }

        foreach (static::dto($analytics)->perBaseCurrency as $baseCurrency => $dto) {
            self::calculateAfterTradesCumulativeReturns($dto, $analytics->counts->forBaseCurrency($baseCurrency));
        }

        foreach (static::dto($analytics)->perQuoteCurrency as $quoteCurrency => $dto) {
            self::calculateAfterTradesCumulativeReturns($dto, $analytics->counts->forQuoteCurrency($quoteCurrency));
        }
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->cumulativeReturn->gross;
    }

    protected static function getReturnFromTrade(Trade $trade): NumericValueAsString
    {
        return $trade->roi(asPercentage: false);
    }

    protected static function calculatePerTradeCumulativeReturns(Trade $trade, NumericDirectionalAggregates $dto): void
    {
        static::initializeBeforeCalculations($dto->total->total);
        static::initializeBeforeCalculations($dto->total->average);

        $adjustedTradeReturn = static::getReturnFromTrade($trade)->add(
            value: 1,
            immutable: true
        );

        $dto->total->average->multiply($adjustedTradeReturn);

        $dto->total->total->multiply(
            value: $adjustedTradeReturn,
        );

        $return = static::calculateFinalCumulativeReturn($dto->total->total);

        if ($return->isGreaterThan($dto->total->highest)) {
            $dto->total->highest->set(value: $return, scale: 2);
            $dto->total->highestPair = $trade->pair();
        }

        if ($return->isLessThan($dto->total->lowest) || $dto->total->lowest->isZero()) {
            $dto->total->lowest->set(value: $return, scale: 2);
            $dto->total->lowestPair = $trade->pair();
        }

        if ($trade->direction->isBuy()) {
            static::initializeBeforeCalculations($dto->buy->total);
            static::initializeBeforeCalculations($dto->buy->average);

            $dto->buy->average->multiply($adjustedTradeReturn);

            $dto->buy->total->multiply(
                value: $adjustedTradeReturn,
            );

            $return = static::calculateFinalCumulativeReturn($dto->buy->total);

            if ($return->isGreaterThan($dto->buy->highest)) {
                $dto->buy->highest->set(value: $return, scale: 2);
                $dto->buy->highestPair = $trade->pair();
            }

            if ($return->isLessThan($dto->buy->lowest) || $dto->buy->lowest->isZero()) {
                $dto->buy->lowest->set(value: $return, scale: 2);
                $dto->buy->lowestPair = $trade->pair();
            }
        } else {
            static::initializeBeforeCalculations($dto->sell->total);
            static::initializeBeforeCalculations($dto->sell->average);

            $dto->sell->average->multiply($adjustedTradeReturn);

            $dto->sell->total->multiply(
                value: $adjustedTradeReturn,
            );

            $return = static::calculateFinalCumulativeReturn($dto->sell->total);

            if ($return->isGreaterThan($dto->sell->highest)) {
                $dto->sell->highest->set(value: $return, scale: 2);
                $dto->sell->highestPair = $trade->pair();
            }

            if ($return->isLessThan($dto->sell->lowest) || $dto->sell->lowest->isZero()) {
                $dto->sell->lowest->set(value: $return, scale: 2);
                $dto->sell->lowestPair = $trade->pair();
            }
        }
    }

    protected static function calculateAfterTradesCumulativeReturns(NumericDirectionalAggregates $dto, NumericByDirections $counts): void
    {
        $dto->total->total->set(
            value: static::calculateFinalCumulativeReturn($dto->total->total),
            scale: 2,
        );

        $dto->buy->total->set(
            value: static::calculateFinalCumulativeReturn($dto->buy->total),
            scale: 2,
        );

        $dto->sell->total->set(
            value: static::calculateFinalCumulativeReturn($dto->sell->total),
            scale: 2,
        );

        $averageCumulativeReturn = static::calculateAverageCumulativeReturn(
            return: $dto->total->average,
            count: $counts->total
        );

        $dto->total->average->set(value: $averageCumulativeReturn, scale: 2);

        $averageCumulativeReturn = static::calculateAverageCumulativeReturn(
            return: $dto->buy->average,
            count: $counts->buy
        );

        $dto->buy->average->set(value: $averageCumulativeReturn, scale: 2);

        $averageCumulativeReturn = static::calculateAverageCumulativeReturn(
            return: $dto->sell->average,
            count: $counts->sell
        );

        $dto->sell->average->set(value: $averageCumulativeReturn, scale: 2);
    }

    protected static function initializeBeforeCalculations(NumericValueAsString $value): void
    {
        if ($value->isZero()) {
            $value->set(1);
        }
    }

    protected static function calculateFinalCumulativeReturn(NumericValueAsString $value): NumericValueAsString
    {
        if ($value->wasChanged()) {
            return $value->subtract(value: 1, immutable: true)
                ->multiply(100)
                ->round(2);
        }

        return new NumericValueAsString(scale: 2);
    }

    protected static function calculateAverageCumulativeReturn(NumericValueAsString $return, NumericValueAsString $count): NumericValueAsString
    {
        if ($count->isZero()) {
            return new NumericValueAsString(scale: 2);
        }

        $isNegative = $return->isLessThan(0);
        $geometricMean = pow($return->abs(immutable: true)->toFloat(), 1 / $count->toInt()) - 1;

        $averageCumulativeReturn = new NumericValueAsString($geometricMean);

        return $averageCumulativeReturn->multiply($isNegative ? -100 : 100)->round(2);
    }
}
