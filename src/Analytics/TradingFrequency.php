<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

/**
 * How often trades are opened, expressed in the largest unit it fits (per hour, day, week,
 * month or year). The average gap is taken over the span of open times, so the input order
 * does not matter.
 *
 * Fewer than two trades, or trades that all opened in the same second, leave no gap to
 * measure: the frequency stays 0 with no unit.
 */
class TradingFrequency implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        $timestamp = $trade->openTime->getTimestamp();

        foreach (static::keys($trade) as $key) {
            $analytics->frequency->record($key, $timestamp);
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        $frequency = $analytics->frequency;

        static::calculateFrequencyFor($analytics, 'global', $frequency->total);

        foreach ($frequency->keysOf('pair') as $pair) {
            static::calculateFrequencyFor($analytics, "pair:{$pair}", $frequency->forPair($pair));
        }

        foreach ($frequency->keysOf('base') as $baseCurrency) {
            static::calculateFrequencyFor($analytics, "base:{$baseCurrency}", $frequency->forBaseCurrency($baseCurrency));
        }

        foreach ($frequency->keysOf('quote') as $quoteCurrency) {
            static::calculateFrequencyFor($analytics, "quote:{$quoteCurrency}", $frequency->forQuoteCurrency($quoteCurrency));
        }
    }

    /**
     * The keys a trade is counted under. Each breakdown has its own namespace, so BTC as the
     * base of BTC/USDT and BTC as the quote of ETH/BTC are two keys, not one.
     *
     * @return list<string>
     */
    protected static function keys(Trade $trade): array
    {
        return ['global', "pair:{$trade->pair()}", "base:{$trade->baseCurrency}", "quote:{$trade->quoteCurrency}"];
    }

    protected static function calculateFrequencyFor(Analytics $analytics, string $key, NumericValueAsString $dto): void
    {
        $opens = $analytics->frequency->opens($key);
        $span = $analytics->frequency->span($key);

        if ($opens < 2 || $span === 0) {
            return;
        }

        $frequency = static::calculateFrequency($span / ($opens - 1));

        $dto->set($frequency)->suffix($frequency->suffix);
    }

    /** @param  float  $gap  the average seconds between two opens, above zero */
    protected static function calculateFrequency(float $gap): NumericValueAsString
    {
        $secondsInHour = 60 * 60;
        $secondsInDay = $secondsInHour * 24;
        $secondsInWeek = $secondsInDay * 7;
        $secondsInMonth = $secondsInDay * 31;
        $secondsInYear = $secondsInMonth * 12;

        [$unit, $suffix] = match (true) {
            $gap <= $secondsInHour => [$secondsInHour, 'per hour'],
            $gap <= $secondsInDay => [$secondsInDay, 'per day'],
            $gap <= $secondsInWeek => [$secondsInWeek, 'per week'],
            $gap <= $secondsInMonth => [$secondsInMonth, 'per month'],
            default => [$secondsInYear, 'per year'],
        };

        return new NumericValueAsString(value: round($unit / $gap, 1), suffix: $suffix);
    }
}
