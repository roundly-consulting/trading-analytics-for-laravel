<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

it('returns whether analytics has been calculated', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);

    expect($analytics->hasBeenCalculated())->toBeFalse();

    $analytics->calculate();

    expect($analytics->hasBeenCalculated())->toBeTrue();
})->with('empty-trades');

it('returns empty analytics as array when they were not calculated', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);

    expect($analytics->toArray())
        ->toBeArray()
        ->toBeEmpty();
})->with('empty-trades');

it('returns calculated analytics as array', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->toArray())
        ->toBeArray()
        ->toHaveKeys([
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
        ]);
})->with('empty-trades');

it('can override per trade and after trades callbacks', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);

    $perTradeCalculators = [];
    $afterTradesCalculators = [];

    Analytics::calculatePerTradeUsing(function (Analytics $analytics, string $calculator, Trade $trade) use (&$perTradeCalculators) {
        $perTradeCalculators[class_basename($calculator)] = 1;
    });

    Analytics::calculateAfterTradesUsing(function (Analytics $analytics, string $calculator) use (&$afterTradesCalculators) {
        $afterTradesCalculators[class_basename($calculator)] = 2;
    });

    $analytics->calculate();

    expect($perTradeCalculators)->toBe([
        'Counts' => 1,
        'TradingVolume' => 1,
        'TradingValue' => 1,
        'Commissions' => 1,
        'UnrealizedGrossProfitAndLoss' => 1,
        'UnrealizedNetProfitAndLoss' => 1,
        'RealizedGrossProfitAndLoss' => 1,
        'RealizedNetProfitAndLoss' => 1,
        'Wins' => 1,
        'ProfitFactor' => 1,
        'GrossCumulativeReturn' => 1,
        'NetCumulativeReturn' => 1,
        'TradingFrequency' => 1,
        'TradesDuration' => 1,
        'Streaks' => 1,
    ]);

    expect($afterTradesCalculators)->toBe([
        'Counts' => 2,
        'TradingVolume' => 2,
        'TradingValue' => 2,
        'Commissions' => 2,
        'UnrealizedGrossProfitAndLoss' => 2,
        'UnrealizedNetProfitAndLoss' => 2,
        'RealizedGrossProfitAndLoss' => 2,
        'RealizedNetProfitAndLoss' => 2,
        'Wins' => 2,
        'ProfitFactor' => 2,
        'GrossCumulativeReturn' => 2,
        'NetCumulativeReturn' => 2,
        'TradingFrequency' => 2,
        'TradesDuration' => 2,
        'Streaks' => 2,
    ]);

    expect($analytics)
        ->hasBeenCalculated()->toBeTrue()
        ->toArray()->toHaveKeys([
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
        ]);

    Analytics::calculatePerTradesNormally();
    Analytics::calculateAfterTradesNormally();
})->with('default-trades');
