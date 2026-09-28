<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Period;
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
    $analytics = Analytics::make($trades)->only([Analytics\ProfitFactor::class])->calculate();

    expect($analytics)
        ->profitFactor->not->toBeNull()
        ->realizedProfitAndLoss->not->toBeNull() // pulled in as a dependency of ProfitFactor
        ->counts->toBeNull()
        ->volume->toBeNull()
        ->streaks->toBeNull()
        ->expectancy->toBeNull();

    expect($analytics->toArray())
        ->toHaveKeys(['profit_factor', 'profit_and_loss'])
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

it('serializes the analytics result to json', function (LazyCollection $trades) {
    $analytics = Analytics::make($trades)->calculate();

    expect($analytics->toJson())->toBe(json_encode($analytics->toArray()))
        ->and(json_encode($analytics))->toBe($analytics->toJson());
})->with('default-trades');

it('implements the laravel arrayable and jsonable contracts', function (LazyCollection $trades) {
    $analytics = Analytics::make($trades);

    expect($analytics)
        ->toBeInstanceOf(Arrayable::class)
        ->toBeInstanceOf(Jsonable::class)
        ->toBeInstanceOf(JsonSerializable::class);
})->with('empty-trades');

it('lists the available metric calculators', function (LazyCollection $trades) use ($expectedCalculators) {
    $metrics = Analytics::make($trades)->metrics();

    expect($metrics)->toHaveCount(count($expectedCalculators))
        ->and(array_map(class_basename(...), $metrics))->toBe($expectedCalculators);
})->with('empty-trades');

it('reads the default scale from config', function (LazyCollection $trades) {
    config()->set('trading-analytics.scale', 5);

    expect(Analytics::make($trades)->getScale())->toBe(5);
})->with('empty-trades');

it('lets an explicit scale override config', function (LazyCollection $trades) {
    config()->set('trading-analytics.scale', 5);

    expect(Analytics::make($trades)->scale(8)->getScale())->toBe(8);
})->with('empty-trades');

it('reads the default win-rate period from config', function (LazyCollection $trades) {
    config()->set('trading-analytics.win_rate_period', 'weekly');

    $analytics = Analytics::make($trades)->only([Analytics\WinRateByPeriod::class])->calculate();

    expect($analytics->winRateByPeriod?->period)
        ->toBe(Period::WEEKLY);
})->with('default-trades');

it('falls back to daily on an invalid configured period', function (LazyCollection $trades) {
    config()->set('trading-analytics.win_rate_period', 'hourly');

    $analytics = Analytics::make($trades)->only([Analytics\WinRateByPeriod::class])->calculate();

    expect($analytics->winRateByPeriod?->period)
        ->toBe(Period::DAILY);
})->with('default-trades');

it('uses the literal default scale when no config value is set', function (LazyCollection $trades) {
    config()->set('trading-analytics.scale', null);

    expect(Analytics::make($trades)->getScale())->toBe(10);
})->with('empty-trades');
