<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\ProfitFactor;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

/**
 * Every figure here is worked out by hand from four trades, independently of the engine, so a
 * wrong formula cannot hide behind a frozen hash.
 *
 * | # | pair    | side | open → close | size | fee  | opened           | closed           | gross | net  |
 * |---|---------|------|--------------|------|------|------------------|------------------|-------|------|
 * | 1 | BTC/USD | buy  | 100 → 110    | 1    | 1    | 2024-01-01 10:00 | 2024-01-01 12:00 | +10   | +9   |
 * | 2 | BTC/USD | sell | 200 → 210    | 1    | 1    | 2024-01-02 10:00 | 2024-01-02 11:00 | −10   | −11  |
 * | 3 | ETH/USD | buy  | 50 → 60      | 2    | 0    | 2024-01-03 10:00 | 2024-01-03 14:00 | +20   | +20  |
 * | 4 | ETH/USD | buy  | 50 → 40      | 1    | none | 2024-01-04 10:00 | open             | −10   | −10  |
 *
 * Trades 1–3 are realized (gross +30 / −10, net +29 / −11); trade 4 is the only open one.
 *
 * @return LazyCollection<int, Trade>
 */
function handComputedTrades(): LazyCollection
{
    return LazyCollection::make([
        Trade::make('BTC', 'USD', '100', '110', '1', 'buy', '2024-01-01 10:00:00', '1', '2024-01-01 12:00:00'),
        Trade::make('BTC', 'USD', '200', '210', '1', 'sell', '2024-01-02 10:00:00', '1', '2024-01-02 11:00:00'),
        Trade::make('ETH', 'USD', '50', '60', '2', 'buy', '2024-01-03 10:00:00', '0', '2024-01-03 14:00:00'),
        Trade::make('ETH', 'USD', '50', '40', '1', 'buy', '2024-01-04 10:00:00'),
    ]);
}

function handComputed(): Analytics
{
    return Analytics::for(handComputedTrades())->calculate();
}

it('keeps realized and unrealized profits and losses apart', function (): void {
    $realized = handComputed()->realizedProfitAndLoss;
    $unrealized = handComputed()->unrealizedProfitAndLoss;

    expect($realized?->grossProfits->total->toRawString())->toBe('30.0000000000')
        ->and($realized?->grossLosses->total->toRawString())->toBe('-10.0000000000')
        ->and($realized?->netProfits->total->toRawString())->toBe('29.0000000000')
        ->and($realized?->netLosses->total->toRawString())->toBe('-11.0000000000')
        ->and($realized?->grossLosses->forPair('ETH/USD')->toRawString())->toBe('0.0000000000')
        ->and($unrealized?->grossProfits->total->toRawString())->toBe('0.0000000000')
        ->and($unrealized?->grossLosses->total->toRawString())->toBe('-10.0000000000')
        ->and($unrealized?->netLosses->total->toRawString())->toBe('-10.0000000000')
        ->and($unrealized?->grossProfits->perPair)->toBe([])
        ->and($unrealized?->grossLosses->forPair('BTC/USD')->toRawString())->toBe('0.0000000000');
});

it('takes the profit factor from realized trades only', function (): void {
    // Realized gross profit 30 over realized gross loss 10; the open −10 is not realized.
    $profitFactor = handComputed()->profitFactor;

    expect($profitFactor?->total->toRawString())->toBe('3.00')
        ->and($profitFactor?->forPair('BTC/USD')->toRawString())->toBe('1.00')
        ->and($profitFactor?->forBaseCurrency('BTC')->toRawString())->toBe('1.00')
        ->and($profitFactor?->forQuoteCurrency('USD')->toRawString())->toBe('3.00');
});

it('reports a profit factor for a history of closed trades only', function (): void {
    $analytics = Analytics::for(handComputedTrades()->filter(fn (Trade $trade): bool => $trade->isRealized())->values())
        ->only([ProfitFactor::class])
        ->calculate();

    expect($analytics->profitFactor?->total->toRawString())->toBe('3.00');
});
