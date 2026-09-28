<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Commissions;
use RoundlyConsulting\TradingAnalytics\Analytics\Counts;
use RoundlyConsulting\TradingAnalytics\Analytics\Expectancy;
use RoundlyConsulting\TradingAnalytics\Analytics\GrossCumulativeReturn;
use RoundlyConsulting\TradingAnalytics\Analytics\MaxDrawdown;
use RoundlyConsulting\TradingAnalytics\Analytics\NetCumulativeReturn;
use RoundlyConsulting\TradingAnalytics\Analytics\ProfitFactor;
use RoundlyConsulting\TradingAnalytics\Analytics\RealizedGrossProfitAndLoss;
use RoundlyConsulting\TradingAnalytics\Analytics\RealizedNetProfitAndLoss;
use RoundlyConsulting\TradingAnalytics\Analytics\RiskAdjustedReturns;
use RoundlyConsulting\TradingAnalytics\Analytics\RiskRewardRatio;
use RoundlyConsulting\TradingAnalytics\Analytics\Streaks;
use RoundlyConsulting\TradingAnalytics\Analytics\TradesDuration;
use RoundlyConsulting\TradingAnalytics\Analytics\TradingFrequency;
use RoundlyConsulting\TradingAnalytics\Analytics\TradingValue;
use RoundlyConsulting\TradingAnalytics\Analytics\TradingVolume;
use RoundlyConsulting\TradingAnalytics\Analytics\UnrealizedGrossProfitAndLoss;
use RoundlyConsulting\TradingAnalytics\Analytics\UnrealizedNetProfitAndLoss;
use RoundlyConsulting\TradingAnalytics\Analytics\WinRateByPeriod;
use RoundlyConsulting\TradingAnalytics\Analytics\Wins;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradeDatasets;

/**
 * `only([X])` and `except([X])` are public API, so every calculator the engine lists in
 * `metrics()` must run alone — with whatever it depends on pulled in — and every calculator
 * must survive the removal of any other, producing exactly the figures of a full run.
 *
 * The dataset mixes pairs, both directions, winners, losers, commissions and an open trade.
 */

/**
 * The slice of `Analytics::toArray()` each calculator writes. Gross and net calculators share
 * one result object, so each owns only its half.
 *
 * @return array<class-string<AnalyticsInterface>, string>
 */
function calculatorOutputs(): array
{
    return [
        Counts::class => 'counts',
        TradingVolume::class => 'volume',
        TradingValue::class => 'value',
        Commissions::class => 'commission',
        UnrealizedGrossProfitAndLoss::class => 'profit_and_loss.unrealized.gross',
        UnrealizedNetProfitAndLoss::class => 'profit_and_loss.unrealized.net',
        RealizedGrossProfitAndLoss::class => 'profit_and_loss.realized.gross',
        RealizedNetProfitAndLoss::class => 'profit_and_loss.realized.net',
        Wins::class => 'wins',
        ProfitFactor::class => 'profit_factor',
        GrossCumulativeReturn::class => 'cumulative_return.gross',
        NetCumulativeReturn::class => 'cumulative_return.net',
        TradingFrequency::class => 'frequency',
        TradesDuration::class => 'duration',
        Streaks::class => 'streaks',
        Expectancy::class => 'expectancy',
        RiskRewardRatio::class => 'risk_reward_ratio',
        WinRateByPeriod::class => 'win_rate_by_period',
        MaxDrawdown::class => 'max_drawdown',
        RiskAdjustedReturns::class => 'risk_adjusted_returns',
    ];
}

/** @return array<string, mixed> */
function fullRun(): array
{
    /** @var array<string, mixed>|null $full */
    static $full = null;

    return $full ??= Analytics::for(TradeDatasets::golden('mixed-signs'))->calculate()->toArray();
}

/** @return array<string, array{class-string<AnalyticsInterface>}> */
function everyCalculator(): array
{
    $calculators = [];

    foreach (Analytics::for(TradeDatasets::golden('tiny'))->metrics() as $calculator) {
        $calculators[class_basename($calculator)] = [$calculator];
    }

    return $calculators;
}

it('maps every calculator the engine runs to its output', function (): void {
    expect(array_keys(calculatorOutputs()))->toEqualCanonicalizing(TradingAnalytics::metrics())
        ->and(everyCalculator())->toHaveCount(20);

    foreach (calculatorOutputs() as $path) {
        expect(Arr::get(fullRun(), $path))->toBeArray()->not->toBeEmpty();
    }
});

it('runs each calculator alone with only()', function (string $calculator): void {
    $alone = TradingAnalytics::calculate(TradeDatasets::golden('mixed-signs'), only: [$calculator])->toArray();
    $path = calculatorOutputs()[$calculator];

    expect(Arr::get($alone, $path))->toBe(Arr::get(fullRun(), $path));
})->with(everyCalculator());

it('runs every other calculator with except()', function (string $excluded): void {
    $without = Analytics::for(TradeDatasets::golden('mixed-signs'))->except([$excluded])->calculate()->toArray();

    foreach (calculatorOutputs() as $calculator => $path) {
        if ($calculator !== $excluded) {
            expect(Arr::get($without, $path))->toBe(Arr::get(fullRun(), $path), "{$calculator} without {$excluded}");
        }
    }
})->with(everyCalculator());

it('drops an excluded calculator nothing else needs', function (): void {
    $analytics = Analytics::for(TradeDatasets::golden('mixed-signs'))->except([Streaks::class, MaxDrawdown::class])->calculate();

    expect($analytics->streaks)->toBeNull()
        ->and($analytics->maxDrawdown)->toBeNull();
});

it('keeps an excluded calculator that a remaining one depends on', function (): void {
    // The profit factor, expectancy and risk/reward ratio are built from the realized gross P&L.
    $analytics = Analytics::for(TradeDatasets::golden('mixed-signs'))->except([RealizedGrossProfitAndLoss::class])->calculate();

    expect($analytics->realizedProfitAndLoss?->toArray()['gross'])->toBe(fullRun()['profit_and_loss']['realized']['gross']);
});

it('drops a calculator the others no longer need', function (): void {
    // The aggregates count their own trades, so nothing but Wins and TradingFrequency needs Counts.
    $analytics = Analytics::for(TradeDatasets::golden('mixed-signs'))->except([Counts::class, Wins::class, TradingFrequency::class])->calculate();

    expect($analytics->counts)->toBeNull()
        ->and($analytics->realizedProfitAndLoss?->toArray())->toBe(fullRun()['profit_and_loss']['realized']);
});

it('pulls dependencies in', function (): void {
    // ProfitFactor needs the realized gross P&L, which needs nothing else.
    $analytics = TradingAnalytics::calculate(TradeDatasets::golden('mixed-signs'), only: [ProfitFactor::class]);

    expect($analytics->realizedProfitAndLoss)->not->toBeNull()
        ->and($analytics->counts)->toBeNull()
        ->and($analytics->unrealizedProfitAndLoss)->toBeNull()
        ->and($analytics->streaks)->toBeNull();
});
