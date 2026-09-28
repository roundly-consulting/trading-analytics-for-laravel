<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\GrossCumulativeReturn;
use RoundlyConsulting\TradingAnalytics\Analytics\MaxDrawdown;
use RoundlyConsulting\TradingAnalytics\Analytics\NetCumulativeReturn;
use RoundlyConsulting\TradingAnalytics\Analytics\ProfitFactor;
use RoundlyConsulting\TradingAnalytics\Analytics\Streaks;
use RoundlyConsulting\TradingAnalytics\Analytics\TradingFrequency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnorderedTradeSourceException;

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

it('averages each aggregate over the trades that fed it', function (): void {
    $analytics = handComputed();
    $realizedGross = $analytics->realizedProfitAndLoss?->gross;

    // Realized: (10 − 10 + 20) / 3 closed trades, not / 4 trades.
    expect($realizedGross?->global->total->average->toRawString())->toBe('6.6666666666')
        ->and($realizedGross?->global->total->count)->toBe(3)
        ->and($realizedGross?->global->buy->average->toRawString())->toBe('15.0000000000')
        ->and($realizedGross?->global->sell->average->toRawString())->toBe('-10.0000000000')
        ->and($realizedGross?->forPair('ETH/USD')->total->average->toRawString())->toBe('20.0000000000')
        ->and($analytics->realizedProfitAndLoss?->net->global->total->average->toRawString())->toBe('6.0000000000')
        // Unrealized: the one open trade.
        ->and($analytics->unrealizedProfitAndLoss?->gross->global->total->average->toRawString())->toBe('-10.0000000000')
        ->and($analytics->unrealizedProfitAndLoss?->gross->forPair('ETH/USD')->total->average->toRawString())->toBe('-10.0000000000')
        // Duration: (7200 + 3600 + 14400) s over the 3 closed trades.
        ->and($analytics->duration?->global->total->average->toRawString())->toBe('8400.00')
        ->and($analytics->duration?->global->total->highest->toRawString())->toBe('14400.00')
        ->and($analytics->duration?->global->total->lowest->toRawString())->toBe('3600.00')
        // Every trade has a size and a value.
        ->and($analytics->volume?->global->total->average->toRawString())->toBe('1.2500000000')
        ->and($analytics->value?->global->total->average->toRawString())->toBe('112.5000000000');
});

it('treats a trade without a commission as a zero commission', function (): void {
    $commission = handComputed()->commission?->global->total;

    // 1 + 1 + 0 + none over 4 trades.
    expect($commission?->total->toRawString())->toBe('2.0000000000')
        ->and($commission?->average->toRawString())->toBe('0.5000000000')
        ->and($commission?->highest->toRawString())->toBe('1.0000000000')
        ->and($commission?->lowest->toRawString())->toBe('0.0000000000')
        ->and($commission?->count)->toBe(4);
});

it('serializes a pair whose realized p&l nets to zero', function (): void {
    // BTC/USD realized +10 and −10: a real, flat pair, not an empty one.
    $perPair = handComputed()->toArray()['profit_and_loss']['realized']['gross']['pnl']['per_pair'];

    expect($perPair)->toHaveKeys(['BTC/USD', 'ETH/USD'])
        ->and($perPair['BTC/USD']['total']['total'])->toBe('0.0000000000')
        ->and($perPair['BTC/USD']['total']['average'])->toBe('0.0000000000');
});

it('keeps a break-even trade as a real highest or lowest value', function (array $pnls, string $highest, string $lowest): void {
    $trades = LazyCollection::make(array_map(
        static fn (int $pnl, int $day): Trade => Trade::make('BTC', 'USD', '100', (string) (100 + $pnl), '1', 'buy', "2024-01-0{$day} 10:00:00", null, "2024-01-0{$day} 11:00:00"),
        $pnls,
        range(1, count($pnls)),
    ));

    $total = Analytics::for($trades)->calculate()->realizedProfitAndLoss?->gross->global->total;

    expect($total?->highest->toRawString())->toBe($highest)
        ->and($total?->lowest->toRawString())->toBe($lowest);
})->with([
    'break-even then a loss' => [[0, -50], '0.0000000000', '-50.0000000000'],
    'a break-even between two wins' => [[10, 0, 5], '10.0000000000', '0.0000000000'],
    'losses only' => [[-5, -20], '-5.0000000000', '-20.0000000000'],
]);

it('compounds the cumulative return and tracks its running extremes', function (): void {
    // Growth factors 1.1 · 0.95 · 1.2 · 0.8 (the open trade at its close price): running
    // returns +10 %, +4.5 %, +25.4 %, +0.32 %; geometric mean 1.0032^(1/4) − 1 = 0.08 %.
    $gross = handComputed()->cumulativeReturn?->gross->global->total;

    expect($gross?->total->toRawString())->toBe('0.32')
        ->and($gross?->average->toRawString())->toBe('0.08')
        ->and($gross?->highest->toRawString())->toBe('25.40')
        ->and($gross?->lowest->toRawString())->toBe('0.32')
        ->and($gross?->count)->toBe(4);
});

it('tracks a cumulative return that never rises above zero', function (): void {
    // −10 % then −10 %: running −10 % and −19 %, so the highest is −10 %, not an untouched 0.
    $trades = LazyCollection::make([
        Trade::make('BTC', 'USD', '100', '90', '1', 'buy', '2024-01-01 10:00:00', null, '2024-01-01 11:00:00'),
        Trade::make('BTC', 'USD', '100', '90', '1', 'buy', '2024-01-02 10:00:00', null, '2024-01-02 11:00:00'),
    ]);

    $gross = Analytics::for($trades)->calculate()->cumulativeReturn?->gross->global->total;

    expect($gross?->total->toRawString())->toBe('-19.00')
        ->and($gross?->highest->toRawString())->toBe('-10.00')
        ->and($gross?->lowest->toRawString())->toBe('-19.00');
});

it('keeps a total loss in the cumulative return instead of restarting it', function (): void {
    // −100 % leaves nothing to compound: a later +50 % is still −100 % overall.
    $trades = LazyCollection::make([
        Trade::make('BTC', 'USD', '100', '0', '1', 'buy', '2024-01-01 10:00:00', null, '2024-01-01 11:00:00'),
        Trade::make('BTC', 'USD', '100', '150', '1', 'buy', '2024-01-02 10:00:00', null, '2024-01-02 11:00:00'),
    ]);

    $gross = Analytics::for($trades)->calculate()->cumulativeReturn?->gross->global->total;

    expect($gross?->total->toRawString())->toBe('-100.00')
        ->and($gross?->average->toRawString())->toBe('-100.00')
        ->and($gross?->highest->toRawString())->toBe('-100.00')
        ->and($gross?->lowest->toRawString())->toBe('-100.00');
});

it('takes wins and the win ratio over closed trades only', function (): void {
    // 2 winners out of the 3 closed trades; the open trade is neither a win nor a trade here.
    $wins = handComputed()->wins;

    expect($wins?->global->total->toRawString())->toBe('2')
        ->and($wins?->winRatio->global->total->toRawString())->toBe('0.66')
        ->and($wins?->winRatio->global->buy->toRawString())->toBe('1.00')
        ->and($wins?->winRatio->global->sell->toRawString())->toBe('0.00')
        ->and($wins?->winRatio->forPair('BTC/USD')->total->toRawString())->toBe('0.50')
        ->and($wins?->winRatio->forPair('ETH/USD')->total->toRawString())->toBe('1.00')
        ->and(handComputed()->expectancy?->winRate->toRawString())->toBe('0.6666');
});

it('serializes a zero win ratio instead of dropping the key', function (): void {
    $trades = LazyCollection::make([
        Trade::make('BTC', 'USD', '100', '110', '1', 'buy', '2024-01-01 10:00:00', null, '2024-01-01 11:00:00'),
        Trade::make('XRP', 'EUR', '1', '0.9', '100', 'buy', '2024-01-02 10:00:00', null, '2024-01-02 11:00:00'),
    ]);

    $winRatio = Analytics::for($trades)->calculate()->toArray()['wins']['win_ratio'];

    expect($winRatio['per_pair'])->toBe([
        'BTC/USD' => ['total' => '1.00', 'buy' => '1.00', 'sell' => '0.00'],
        'XRP/EUR' => ['total' => '0.00', 'buy' => '0.00', 'sell' => '0.00'],
    ])->and($winRatio['global'])->toBe(['total' => '0.50', 'buy' => '0.50', 'sell' => '0.00']);
});

it('computes expectancy and risk/reward at full precision', function (): void {
    // Average win 30/2 = 15, average loss 10/1 = 10: expectancy (30 − 10)/3, reward/risk 1.5.
    $analytics = handComputed();

    expect($analytics->expectancy?->value->toRawString())->toBe('6.6666666666')
        ->and($analytics->expectancy?->averageWin->toRawString())->toBe('15.0000000000')
        ->and($analytics->expectancy?->averageLoss->toRawString())->toBe('10.0000000000')
        ->and($analytics->expectancy?->lossRate->toRawString())->toBe('0.3333')
        ->and($analytics->riskRewardRatio?->value->toRawString())->toBe('1.5000');
});

it('counts a break-even trade as neither a win nor a loss', function (): void {
    // +100, −50 and a 0: the average loss is 50 (not 25), so reward/risk is 2 (not 4).
    $trades = LazyCollection::make([
        Trade::make('BTC', 'USD', '100', '200', '1', 'buy', '2024-01-01 10:00:00', null, '2024-01-01 11:00:00'),
        Trade::make('BTC', 'USD', '100', '50', '1', 'buy', '2024-01-02 10:00:00', null, '2024-01-02 11:00:00'),
        Trade::make('BTC', 'USD', '100', '100', '1', 'buy', '2024-01-03 10:00:00', null, '2024-01-03 11:00:00'),
    ]);

    $analytics = Analytics::for($trades)->calculate();

    expect($analytics->riskRewardRatio?->value->toRawString())->toBe('2.0000')
        ->and($analytics->riskRewardRatio?->averageLoss->toRawString())->toBe('50.0000000000')
        ->and($analytics->riskRewardRatio?->losingTrades)->toBe(1)
        // Expectancy stays the average P&L of all 3 closed trades: (100 − 50 + 0) / 3.
        ->and($analytics->expectancy?->value->toRawString())->toBe('16.6666666666')
        ->and($analytics->expectancy?->averageLoss->toRawString())->toBe('50.0000000000')
        ->and($analytics->expectancy?->winRate->toRawString())->toBe('0.3333')
        ->and($analytics->expectancy?->lossRate->toRawString())->toBe('0.3333')
        ->and($analytics->expectancy?->breakEvenTrades)->toBe(1)
        ->and($analytics->streaks?->losses->global->total->toRawString())->toBe('1');
});

it('breaks both streaks on a break-even trade', function (): void {
    // win, break-even, win, loss, break-even, loss: no two outcomes in a row.
    $pnls = [10, 0, 10, -10, 0, -10];
    $trades = LazyCollection::make(array_map(
        static fn (int $pnl, int $day): Trade => Trade::make('BTC', 'USD', '100', (string) (100 + $pnl), '1', 'buy', "2024-01-0{$day} 10:00:00", null, "2024-01-0{$day} 11:00:00"),
        $pnls,
        range(1, count($pnls)),
    ));

    $streaks = Analytics::for($trades)->only([Streaks::class])->calculate()->streaks;

    expect($streaks?->wins->global->total->toRawString())->toBe('1')
        ->and($streaks?->losses->global->total->toRawString())->toBe('1');
});

it('measures trading frequency over the span of open times', function (): void {
    // Four opens a day apart: 3 days over 3 gaps.
    $frequency = handComputed()->frequency;

    expect((string) $frequency?->total)->toBe('1.0 per day')
        ->and((string) $frequency?->forPair('BTC/USD'))->toBe('1.0 per day')
        ->and((string) $frequency?->forQuoteCurrency('USD'))->toBe('1.0 per day');
});

it('reads trading frequency the same whatever order the trades arrive in', function (): void {
    $trades = [
        Trade::make('BTC', 'USD', '1', '2', '1', 'buy', '2024-01-03 10:00:00'),
        Trade::make('BTC', 'USD', '1', '2', '1', 'buy', '2024-01-01 10:00:00'),
    ];

    $frequency = Analytics::for(LazyCollection::make($trades))->only([TradingFrequency::class])->calculate()->frequency;

    // Two days between the two opens, not a negative gap.
    expect((string) $frequency?->total)->toBe('3.5 per week');
});

it('handles trades opened in the same second', function (): void {
    $sameSecond = static fn (int $count, string $time): array => array_map(
        static fn (): Trade => Trade::make('BTC', 'USD', '1', '2', '1', 'buy', $time),
        range(1, $count),
    );

    $allAtOnce = Analytics::for(LazyCollection::make($sameSecond(3, '2024-01-01 10:00:00')))->only([TradingFrequency::class])->calculate();
    $subSecond = Analytics::for(LazyCollection::make([...$sameSecond(2, '2024-01-01 10:00:00'), ...$sameSecond(1, '2024-01-01 10:00:01')]))->only([TradingFrequency::class])->calculate();

    // No gap to measure: undefined, reported like a single trade (0, no unit).
    expect((string) $allAtOnce->frequency?->total)->toBe('0.0')
        // 1 s over 2 gaps is a trade every 0.5 s.
        ->and((string) $subSecond->frequency?->total)->toBe('7200.0 per hour');
});

it('keeps a currency that is a base in one pair apart from the same currency as a quote', function (): void {
    $analytics = Analytics::for(LazyCollection::make([
        // BTC as the quote: two trades a day apart, both winners.
        Trade::make('ETH', 'BTC', '1', '2', '1', 'buy', '2024-01-01 00:00:00', null, '2024-01-01 00:30:00'),
        Trade::make('ETH', 'BTC', '1', '2', '1', 'buy', '2024-01-02 00:00:00', null, '2024-01-02 00:30:00'),
        // BTC as the base: two trades an hour apart, both winners.
        Trade::make('BTC', 'USDT', '1', '2', '1', 'buy', '2024-01-10 00:00:00', null, '2024-01-10 00:30:00'),
        Trade::make('BTC', 'USDT', '1', '2', '1', 'buy', '2024-01-10 01:00:00', null, '2024-01-10 01:30:00'),
    ]))->only([TradingFrequency::class, Streaks::class])->calculate();

    expect((string) $analytics->frequency?->forQuoteCurrency('BTC'))->toBe('1.0 per day')
        ->and((string) $analytics->frequency?->forBaseCurrency('BTC'))->toBe('1.0 per hour')
        ->and($analytics->streaks?->wins->forQuoteCurrency('BTC')->total->toRawString())->toBe('2')
        ->and($analytics->streaks?->wins->forBaseCurrency('BTC')->total->toRawString())->toBe('2')
        ->and($analytics->streaks?->wins->global->total->toRawString())->toBe('4');
});

it('derives drawdown and risk-adjusted ratios at full precision', function (): void {
    // Net equity +9, −2, +18: an 11 drop from a 9 peak is 122.2222…%, not 11.0000 / 9.0000
    // truncated to 1.2222 first.
    // Net returns 0.09, −0.055, 0.2: mean 0.0783333…, population deviation 0.1044296…,
    // downside deviation 0.055 / √3 = 0.0317542…
    $analytics = handComputed();

    expect($analytics->maxDrawdown?->value->toRawString())->toBe('11.0000000000')
        ->and($analytics->maxDrawdown?->percentage->toRawString())->toBe('122.2222')
        ->and($analytics->riskAdjustedReturns?->meanReturn->toRawString())->toBe('0.0783333333')
        ->and($analytics->riskAdjustedReturns?->standardDeviation->toRawString())->toBe('0.1044296679')
        ->and($analytics->riskAdjustedReturns?->downsideDeviation->toRawString())->toBe('0.0317542648')
        ->and($analytics->riskAdjustedReturns?->sharpeRatio->toRawString())->toBe('0.7501')
        ->and($analytics->riskAdjustedReturns?->sortinoRatio->toRawString())->toBe('2.4668');
});

it('divides ratios whose inputs are smaller than 0.0001 instead of crashing', function (): void {
    $trade = static fn (string $close, int $day): Trade => Trade::make('ETH', 'BTC', '0.05', $close, '1', 'buy', "2024-01-0{$day} 10:00:00", null, "2024-01-0{$day} 11:00:00");

    // P&L +0.001 then −0.00005 BTC: reward/risk 0.001 / 0.00005.
    $riskReward = Analytics::for(LazyCollection::make([$trade('0.051', 1), $trade('0.04995', 2)]))->calculate()->riskRewardRatio;
    // Equity peaks at +0.00005, then falls to −0.00005: a 0.0001 drop, 200 % of the peak.
    $drawdown = Analytics::for(LazyCollection::make([$trade('0.05005', 1), $trade('0.0499', 2)]))->calculate()->maxDrawdown;
    // Returns +0.1 %, +0.2 %, −0.005 %: downside deviation √(0.00005² / 3) = 0.0000288675…
    $returns = Analytics::for(LazyCollection::make([
        Trade::make('X', 'USD', '100', '100.1', '1', 'buy', '2024-01-01 10:00:00', null, '2024-01-01 11:00:00'),
        Trade::make('X', 'USD', '100', '100.2', '1', 'buy', '2024-01-02 10:00:00', null, '2024-01-02 11:00:00'),
        Trade::make('X', 'USD', '100', '99.995', '1', 'buy', '2024-01-03 10:00:00', null, '2024-01-03 11:00:00'),
    ]))->calculate()->riskAdjustedReturns;

    expect($riskReward?->value->toRawString())->toBe('20.0000')
        ->and($drawdown?->percentage->toRawString())->toBe('200.0000')
        ->and($returns?->sharpeRatio->toRawString())->toBe('1.1748')
        ->and($returns?->sortinoRatio->toRawString())->toBe('34.0636');
});

it('refuses realized trades that arrive out of close-time order', function (): void {
    // Trade 2 closed before trade 1: the drawdown and the streaks would follow a sequence
    // that never happened.
    $trades = handComputedTrades()->all();

    expect(fn () => Analytics::for(LazyCollection::make([$trades[1], $trades[0], $trades[2]]))->calculate())
        ->toThrow(UnorderedTradeSourceException::class, 'a trade closed at [2024-01-01 12:00:00] arrived after one closed at [2024-01-02 11:00:00]');
});

it('checks the order only for the calculators that follow it', function (): void {
    $trades = handComputedTrades()->all();
    $reversed = LazyCollection::make(array_reverse($trades));

    $analytics = Analytics::for($reversed)->except([MaxDrawdown::class, Streaks::class, GrossCumulativeReturn::class, NetCumulativeReturn::class])->calculate();

    expect($analytics->realizedProfitAndLoss?->grossProfits->total->toRawString())->toBe('30.0000000000')
        ->and($analytics->maxDrawdown)->toBeNull();
});

it('lets open trades and equal close times come in any order', function (): void {
    [$first, $second, $third, $open] = handComputedTrades()->all();
    $sameClose = Trade::make('ETH', 'USD', '50', '55', '1', 'buy', '2024-01-03 09:00:00', null, '2024-01-03 14:00:00');

    $analytics = Analytics::for(LazyCollection::make([$open, $first, $second, $third, $sameClose]))->calculate();

    expect($analytics->maxDrawdown?->value->toRawString())->toBe('11.0000000000')
        ->and($analytics->streaks?->wins->global->total->toRawString())->toBe('2');
});
