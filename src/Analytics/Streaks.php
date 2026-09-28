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
     * Each breakdown has its own key namespace: BTC as the base of BTC/USDT and BTC as the
     * quote of ETH/BTC run two streaks, not one.
     *
     * @return array<string, callable(): NumericValueAsString>
     */
    protected static function scopes(NumericDirectionalByCurrency $longest, Trade $trade): array
    {
        $direction = $trade->direction->value;
        $pair = $trade->pair();
        $base = $trade->baseCurrency;
        $quote = $trade->quoteCurrency;
        $side = static fn (NumericByDirections $counts): NumericValueAsString => $trade->direction->isBuy() ? $counts->buy : $counts->sell;

        return [
            'global:total' => static fn (): NumericValueAsString => $longest->global->total,
            "global:{$direction}" => static fn (): NumericValueAsString => $side($longest->global),
            "pair:{$pair}:total" => static fn (): NumericValueAsString => $longest->forPair($pair)->total,
            "pair:{$pair}:{$direction}" => static fn (): NumericValueAsString => $side($longest->forPair($pair)),
            "base:{$base}:total" => static fn (): NumericValueAsString => $longest->forBaseCurrency($base)->total,
            "base:{$base}:{$direction}" => static fn (): NumericValueAsString => $side($longest->forBaseCurrency($base)),
            "quote:{$quote}:total" => static fn (): NumericValueAsString => $longest->forQuoteCurrency($quote)->total,
            "quote:{$quote}:{$direction}" => static fn (): NumericValueAsString => $side($longest->forQuoteCurrency($quote)),
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
