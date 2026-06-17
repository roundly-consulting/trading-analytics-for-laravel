<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

class TradingFrequency implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        static::prepareTimestampsForKey($analytics, 'total', $trade);
        static::prepareTimestampsForKey($analytics, $trade->pair(), $trade);
        static::prepareTimestampsForKey($analytics, $trade->quoteCurrency, $trade);
        static::prepareTimestampsForKey($analytics, $trade->baseCurrency, $trade);
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        static::calculateFrequencyFor($analytics, $analytics->counts->global->total, $analytics->frequency->total, 'total');

        foreach ($analytics->counts->perPair as $pair => $count) {
            static::calculateFrequencyFor($analytics, $count->total, $analytics->frequency->forPair($pair), $pair);
        }

        foreach ($analytics->counts->perQuoteCurrency as $quoteCurrency => $count) {
            static::calculateFrequencyFor($analytics, $count->total, $analytics->frequency->forQuoteCurrency($quoteCurrency), $quoteCurrency);
        }

        foreach ($analytics->counts->perBaseCurrency as $baseCurrency => $count) {
            static::calculateFrequencyFor($analytics, $count->total, $analytics->frequency->forBaseCurrency($baseCurrency), $baseCurrency);
        }
    }

    protected static function prepareTimestampsForKey(Analytics $analytics, string $key, Trade $trade): void
    {
        $lastTimestamp = $analytics->frequency->getLastTradeTimestamp($key);

        if ($lastTimestamp !== 0) {
            $analytics->frequency->incrementTimeDifference(
                key: $key,
                by: $trade->openTime->getTimestamp() - $lastTimestamp
            );
        }

        $analytics->frequency->setLastTradeTimestamp(key: $key, timestamp: $trade->openTime->getTimestamp());
    }

    protected static function calculateFrequencyFor(Analytics $analytics, NumericValueAsString $count, NumericValueAsString $dto, string $key): void
    {
        if ($count->isGreaterThan(1)) {
            $frequency = static::calculateFrequency(
                (int) floor($analytics->frequency->getTimeDifference($key) / ($count->toInt() - 1))
            );

            $dto->set($frequency)->suffix($frequency->suffix);
        }
    }

    protected static function calculateFrequency(int $difference): NumericValueAsString
    {
        $secondsInHour = 60 * 60;
        $secondsInDay = $secondsInHour * 24;
        $secondsInWeek = $secondsInDay * 7;
        $secondsInMonth = $secondsInDay * 31;
        $secondsInYear = $secondsInMonth * 12;

        return match (true) {
            $difference <= $secondsInHour => new NumericValueAsString(
                value: round($secondsInHour / $difference, 1),
                suffix: 'per hour',
            ),
            $difference <= $secondsInDay => new NumericValueAsString(
                value: round($secondsInDay / $difference, 1),
                suffix: 'per day',
            ),
            $difference <= $secondsInWeek => new NumericValueAsString(
                value: round($secondsInWeek / $difference, 1),
                suffix: 'per week',
            ),
            $difference <= $secondsInMonth => new NumericValueAsString(
                value: round($secondsInMonth / $difference, 1),
                suffix: 'per month',
            ),
            default => new NumericValueAsString(
                value: round($secondsInYear / $difference, 1),
                suffix: 'per year',
            ),
        };
    }
}
