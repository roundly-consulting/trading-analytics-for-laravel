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
 * Longest runs of consecutive winning and losing closed trades. A break-even trade is
 * neither, so it ends both runs.
 */
class Streaks implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        if ($trade->isOpen()) {
            return;
        }

        $pnl = $trade->profitAndLoss();
        $isWin = $pnl->isPositiveNonZero();
        $longest = $isWin ? $analytics->streaks->wins : $analytics->streaks->losses;

        foreach (static::scopes($longest, $trade) as $key => $resolve) {
            if ($pnl->isZero()) {
                $analytics->streaks->resetCurrent(key: $key, isWin: true);
                $analytics->streaks->resetCurrent(key: $key, isWin: false);

                continue;
            }

            static::resolveStreak($analytics, $resolve(), $key, $isWin);
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        //
    }

    /**
     * Every running-streak key the trade belongs to, each with a resolver for the longest-
     * streak value it feeds. The value is only resolved for a win or a loss, so a break-even
     * trade materialises no empty breakdown entry.
     *
     * @return array<string, callable(): NumericValueAsString>
     */
    protected static function scopes(NumericDirectionalByCurrency $longest, Trade $trade): array
    {
        $direction = $trade->direction->value;
        $side = static fn (NumericByDirections $counts): NumericValueAsString => $trade->direction->isBuy() ? $counts->buy : $counts->sell;

        return [
            'total' => static fn (): NumericValueAsString => $longest->global->total,
            $direction => static fn (): NumericValueAsString => $side($longest->global),
            "{$trade->pair()}_total" => static fn (): NumericValueAsString => $longest->forPair($trade->pair())->total,
            "{$trade->pair()}_{$direction}" => static fn (): NumericValueAsString => $side($longest->forPair($trade->pair())),
            "{$trade->baseCurrency}_total" => static fn (): NumericValueAsString => $longest->forBaseCurrency($trade->baseCurrency)->total,
            "{$trade->baseCurrency}_{$direction}" => static fn (): NumericValueAsString => $side($longest->forBaseCurrency($trade->baseCurrency)),
            "{$trade->quoteCurrency}_total" => static fn (): NumericValueAsString => $longest->forQuoteCurrency($trade->quoteCurrency)->total,
            "{$trade->quoteCurrency}_{$direction}" => static fn (): NumericValueAsString => $side($longest->forQuoteCurrency($trade->quoteCurrency)),
        ];
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
