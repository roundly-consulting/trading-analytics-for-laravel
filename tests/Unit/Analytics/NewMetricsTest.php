<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\RiskAdjustedReturns;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

/**
 * Three realized BUY trades, no commission, so the metrics are easy to verify
 * by hand: win +100, loss -50, win +30. Equity curve: 100, 50, 80.
 */
function mixedWinLossTrades(): LazyCollection
{
    $make = fn (string $close, int $day): Trade => new Trade(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: new NumericValueAsString('10'),
        closePrice: new NumericValueAsString($close),
        size: new NumericValueAsString('10'),
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, $day, 12),
        closeTime: Carbon::create(2024, 1, $day, 13),
    );

    return new LazyCollection([
        $make('20', 1), // +100
        $make('5', 2),  // -50
        $make('13', 3), // +30
    ]);
}

it('computes expectancy from realized wins and losses', function () {
    $analytics = Analytics::make(mixedWinLossTrades())->calculate();

    expect($analytics->expectancy->toArray())->toBe([
        'value' => '26.6640000000',
        'average_win' => '65.0000000000',
        'average_loss' => '50.0000000000',
        'win_rate' => '0.6666',
        'loss_rate' => '0.3333',
    ]);
});

it('computes the risk-reward ratio', function () {
    $analytics = Analytics::make(mixedWinLossTrades())->calculate();

    expect($analytics->riskRewardRatio->toArray())->toBe([
        'value' => '1.3000',
        'average_win' => '65.0000000000',
        'average_loss' => '50.0000000000',
    ]);
});

it('computes the running-peak maximum drawdown', function () {
    $analytics = Analytics::make(mixedWinLossTrades())->calculate();

    expect($analytics->maxDrawdown->toArray())->toBe([
        'value' => '50.0000000000',
        'percentage' => '50.0000',
        'equity' => '80.0000000000',
        'peak' => '100.0000000000',
    ]);
});

it('computes the win rate bucketed by period', function () {
    $analytics = Analytics::make(mixedWinLossTrades())
        ->usingWinRatePeriod(Period::DAILY)
        ->calculate();

    expect($analytics->winRateByPeriod->toArray())->toBe([
        'period' => 'daily',
        'wins' => ['2024-01-01' => 1, '2024-01-03' => 1],
        'totals' => ['2024-01-01' => 1, '2024-01-02' => 1, '2024-01-03' => 1],
        'rates' => ['2024-01-01' => '1.0000', '2024-01-02' => '0.0000', '2024-01-03' => '1.0000'],
    ]);
});

it('computes sharpe and sortino on the multi-pass path', function () {
    $analytics = Analytics::make(mixedWinLossTrades())->calculate();

    expect($analytics->riskAdjustedReturns->toArray())->toBe([
        'sharpe_ratio' => '0.4350',
        'sortino_ratio' => '0.9237',
        'mean_return' => '0.2666666666',
        'standard_deviation' => '0.6128258770',
        'downside_deviation' => '0.2886751345',
        'risk_free_rate' => '0.0000000000',
        'sample_size' => 3,
    ]);
});

it('leaves new metrics empty for an empty trade set', function () {
    $analytics = Analytics::make(new LazyCollection([]))->calculate();

    expect($analytics->expectancy->toArray()['value'])->toBe('0.0000000000')
        ->and($analytics->riskRewardRatio->toArray()['value'])->toBe('0.0000')
        ->and($analytics->maxDrawdown->toArray()['value'])->toBe('0.0000000000')
        ->and($analytics->winRateByPeriod->toArray()['rates'])->toBe([])
        ->and($analytics->riskAdjustedReturns->toArray()['sample_size'])->toBe(0);
});

it('ignores open trades in the new metrics', function () {
    $openTrade = new Trade(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: new NumericValueAsString('10'),
        closePrice: new NumericValueAsString('20'),
        size: new NumericValueAsString('10'),
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
    );

    $analytics = Analytics::make(new LazyCollection([$openTrade]))->calculate();

    expect($analytics->expectancy->winningTrades)->toBe(0)
        ->and($analytics->riskRewardRatio->winningTrades)->toBe(0)
        ->and($analytics->riskAdjustedReturns->toArray()['sample_size'])->toBe(0);
});

it('averages the cumulative return of a long winning run', function () {
    // 1,000 trades of +1% each compound to ~20,959×. The geometric mean used to stall 100
    // Newton steps short of the 1,000th root and report an average of thousands of percent.
    $trades = LazyCollection::times(1000, static fn (int $i): Trade => Trade::make(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: '100',
        closePrice: '101',
        size: '1',
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1)->addMinutes($i),
        closeTime: Carbon::create(2024, 1, 1)->addMinutes($i + 1),
    ));

    $average = Analytics::make($trades)->only([Analytics\GrossCumulativeReturn::class])->calculate()
        ->cumulativeReturn?->gross->global->total->average;

    expect((string) $average)->toBe('1.00');
});

it('folds each realized return into running sums instead of storing it', function () {
    $result = new RiskAdjustedReturns(NumericValueAsString::of('0.01'));

    foreach (['0.10', '-0.05', '0.005'] as $return) {
        $result->recordReturn(NumericValueAsString::of($return));
    }

    expect($result->sampleSize)->toBe(3)
        ->and($result->sumOfReturns->toRawString())->toBe('0.05500000000000000000')
        ->and($result->sumOfSquaredReturns->toRawString())->toBe('0.0125250000'.str_repeat('0', 30))
        // Only the returns below the risk-free rate: (-0.06)² + (-0.005)².
        ->and($result->sumOfSquaredShortfalls->toRawString())->toBe('0.00362500000000000000')
        ->and(get_object_vars($result))->not->toHaveKey('returns');
});
