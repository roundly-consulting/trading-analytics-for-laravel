<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnknownCalculatorException;

$expectedCalculators = [
    'Counts',
    'TradingVolume',
    'TradingValue',
    'Commissions',
    'UnrealizedGrossProfitAndLoss',
    'UnrealizedNetProfitAndLoss',
    'RealizedGrossProfitAndLoss',
    'RealizedNetProfitAndLoss',
    'Wins',
    'ProfitFactor',
    'GrossCumulativeReturn',
    'NetCumulativeReturn',
    'TradingFrequency',
    'TradesDuration',
    'Streaks',
    'Expectancy',
    'RiskRewardRatio',
    'WinRateByPeriod',
    'MaxDrawdown',
    'RiskAdjustedReturns',
];

$expectedKeys = [
    'counts',
    'wins',
    'volume',
    'value',
    'commission',
    'profit_and_loss',
    'profit_factor',
    'cumulative_return',
    'frequency',
    'duration',
    'streaks',
    'expectancy',
    'risk_reward_ratio',
    'win_rate_by_period',
    'max_drawdown',
    'risk_adjusted_returns',
];

it('returns whether analytics has been calculated', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);

    expect($analytics->hasBeenCalculated())->toBeFalse();

    $analytics->calculate();

    expect($analytics->hasBeenCalculated())->toBeTrue();
})->with('empty-trades');

it('returns the instance fluently from calculate', function (LazyCollection $trades) {
    $analytics = Analytics::make($trades);

    expect($analytics->calculate())->toBe($analytics);
})->with('empty-trades');

it('builds via the static factory and scale fluently', function (LazyCollection $trades) {
    $analytics = Analytics::make($trades)->scale(4);

    expect($analytics)
        ->toBeInstanceOf(Analytics::class)
        ->getScale()->toBe(4);

    expect(Analytics::for($trades))->toBeInstanceOf(Analytics::class);
})->with('empty-trades');

it('returns empty analytics as array when they were not calculated', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);

    expect($analytics->toArray())
        ->toBeArray()
        ->toBeEmpty();
})->with('empty-trades');

it('returns calculated analytics as array', function (LazyCollection $trades) use ($expectedKeys) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->toArray())
        ->toBeArray()
        ->toHaveKeys($expectedKeys);
})->with('empty-trades');

it('runs only the requested calculators and their dependencies', function (LazyCollection $trades) {
    $analytics = Analytics::make($trades)->only([Analytics\Wins::class])->calculate();

    expect($analytics)
        ->wins->not->toBeNull()
        ->counts->not->toBeNull() // pulled in as a dependency of Wins
        ->volume->toBeNull()
        ->streaks->toBeNull()
        ->expectancy->toBeNull();

    expect($analytics->toArray())
        ->toHaveKeys(['wins', 'counts'])
        ->not->toHaveKey('volume');
})->with('default-trades');

it('runs every calculator except the excluded ones', function (LazyCollection $trades) {
    $analytics = Analytics::make($trades)->except([Analytics\Streaks::class])->calculate();

    expect($analytics)
        ->streaks->toBeNull()
        ->counts->not->toBeNull();
})->with('default-trades');

it('errors on an unknown calculator', function (LazyCollection $trades) {
    expect(fn () => Analytics::make($trades)->only([stdClass::class]))
        ->toThrow(UnknownCalculatorException::class);
})->with('empty-trades');

it('scopes per-trade hooks to the instance', function (LazyCollection $trades) {
    $touched = [];

    $withHook = Analytics::make($trades)->onEachTrade(function () use (&$touched) {
        $touched[] = 'hooked';
    });

    $withoutHook = Analytics::make($trades);

    $withHook->calculate();
    $withoutHook->calculate();

    // The second instance must not see the first instance's hook.
    expect($touched)->not->toBeEmpty()
        ->and($withoutHook->counts)->not->toBeNull();
})->with('default-trades');

it('runs instance per-trade and after-trades hooks', function (LazyCollection $trades) use ($expectedCalculators) {
    $perTradeCalculators = [];
    $afterTradesCalculators = [];

    $analytics = Analytics::make($trades)
        ->onEachTrade(function (Analytics $analytics, string $calculator, Trade $trade) use (&$perTradeCalculators) {
            $perTradeCalculators[class_basename($calculator)] = 1;
        })
        ->afterTrades(function (Analytics $analytics, string $calculator) use (&$afterTradesCalculators) {
            $afterTradesCalculators[class_basename($calculator)] = 2;
        });

    $analytics->calculate();

    expect(array_keys($perTradeCalculators))->toBe($expectedCalculators);
    expect(array_keys($afterTradesCalculators))->toBe($expectedCalculators);
})->with('default-trades');

it('keeps the deprecated static hooks working', function (LazyCollection $trades) {
    $perTradeCalculators = [];
    $afterTradesCalculators = [];

    Analytics::calculatePerTradeUsing(function (Analytics $analytics, string $calculator, Trade $trade) use (&$perTradeCalculators) {
        $perTradeCalculators[class_basename($calculator)] = 1;
    });

    Analytics::calculateAfterTradesUsing(function (Analytics $analytics, string $calculator) use (&$afterTradesCalculators) {
        $afterTradesCalculators[class_basename($calculator)] = 2;
    });

    try {
        (new Analytics($trades))->calculate();

        expect($perTradeCalculators)->not->toBeEmpty()
            ->and($afterTradesCalculators)->not->toBeEmpty();
    } finally {
        Analytics::calculatePerTradesNormally();
        Analytics::calculateAfterTradesNormally();
    }
})->with('default-trades');
