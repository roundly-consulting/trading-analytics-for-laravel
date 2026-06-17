<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

class Streaks implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        if ($trade->isOpen()) {
            return;
        }

        $isWin = $trade->profitAndLoss()->isPositiveNonZero();

        $baseDto = $isWin ? $analytics->streaks->wins : $analytics->streaks->losses;

        // Total
        static::resolveStreak($analytics, $baseDto->global->total, 'total', $isWin);

        // Total - Direction
        static::resolveStreak($analytics, $baseDto->global->{$trade->direction->value}, $trade->direction->value, $isWin);

        // Per Pair - Total
        static::resolveStreak($analytics, $baseDto->forPair($trade->pair())->total, "{$trade->pair()}_total", $isWin);

        // Per Pair - Direction
        static::resolveStreak($analytics, $baseDto->forPair($trade->pair())->{$trade->direction->value}, "{$trade->pair()}_{$trade->direction->value}", $isWin);

        // Per Base Currency - Total
        static::resolveStreak($analytics, $baseDto->forBaseCurrency($trade->baseCurrency)->total, "{$trade->baseCurrency}_total", $isWin);

        // Per Base Currency - Direction
        static::resolveStreak($analytics, $baseDto->forBaseCurrency($trade->baseCurrency)->{$trade->direction->value}, "{$trade->baseCurrency}_{$trade->direction->value}", $isWin);

        // Per Quote Currency - Total
        static::resolveStreak($analytics, $baseDto->forQuoteCurrency($trade->quoteCurrency)->total, "{$trade->quoteCurrency}_total", $isWin);

        // Per Quote Currency - Direction
        static::resolveStreak($analytics, $baseDto->forQuoteCurrency($trade->quoteCurrency)->{$trade->direction->value}, "{$trade->quoteCurrency}_{$trade->direction->value}", $isWin);
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        //
    }

    protected static function resolveStreak(Analytics $analytics, NumericValueAsString $dto, string $key, bool $isWin): void
    {
        $analytics->streaks->incrementCurrent(key: $key, isWin: $isWin);
        $analytics->streaks->resetCurrent(key: $key, isWin: ! $isWin);

        $currentStreak = $analytics->streaks->current(key: $key, isWin: $isWin);

        if ($dto->isLessThan($currentStreak)) {
            $dto->set($currentStreak);
        }
    }
}
