<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use RoundlyConsulting\TradingAnalytics\Analytics\MaxDrawdown;
use RoundlyConsulting\TradingAnalytics\Analytics\RiskAdjustedReturns;
use RoundlyConsulting\TradingAnalytics\Analytics\Streaks;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradeDatasets;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradesTable;

/**
 * The engine promises constant memory in the length of the trade history: nothing in the
 * pass may keep a per-trade buffer. These runs stream 50,000 trades and bound the peak
 * growth to a few MB — far below what any O(n) buffer costs (the old per-trade return list
 * alone grew ~12 MB here).
 *
 * The rows open a minute apart, so the stream spans ~35 days: win-rate buckets are output
 * sized by the calendar, not a buffer, and a short span keeps them out of the measurement.
 */
const STREAMED_TRADES = 50_000;

/**
 * Xdebug's coverage mode instruments every executed line, which makes these streams ~25×
 * slower (the generator run alone took ~10 minutes on a coverage leg) while adding no
 * coverage. They skip there — visibly — and run wherever the code is not instrumented: every
 * `composer test` (pcov costs nothing until a coverage run starts) and the uninstrumented
 * `test-pgsql` leg of every CI run, which keeps the proof in CI.
 */
function coverageInstrumentationIsActive(): bool
{
    return function_exists('xdebug_info') && in_array('coverage', xdebug_info('mode'), true);
}

const SKIPPED_UNDER_COVERAGE = 'memory proof runs on the uninstrumented legs (Xdebug coverage mode is ~25x slower)';

const MEMORY_BOUND_BYTES = 4 * 1024 * 1024;

/**
 * Run `$run` once on a small stream to load every class and warm every cache, then measure
 * the peak memory growth of the real run.
 *
 * @param  Closure(int): mixed  $run  called with the number of trades to stream
 * @return array{mixed, int}
 */
function peakGrowthOf(Closure $run): array
{
    $run(500);
    gc_collect_cycles();

    memory_reset_peak_usage();
    $baseline = memory_get_usage();

    $result = $run(STREAMED_TRADES);

    return [$result, memory_get_peak_usage() - $baseline];
}

it('streams a 50,000-trade generator through every calculator in constant memory', function (): void {
    [$analytics, $growth] = peakGrowthOf(
        static fn (int $count) => TradingAnalytics::calculate(TradeDatasets::randomRows($count, spacing: 60)),
    );

    expect($analytics->counts?->global->total->toInt())->toBe(STREAMED_TRADES)
        ->and($analytics->riskAdjustedReturns?->sampleSize)->toBeGreaterThan(40_000)
        ->and($growth)->toBeLessThan(MEMORY_BOUND_BYTES);
})->skip(coverageInstrumentationIsActive(), SKIPPED_UNDER_COVERAGE);

it('streams a 50,000-row query builder in 500-row pages in constant memory', function (): void {
    TradesTable::create();
    TradesTable::seed(TradeDatasets::randomRows(STREAMED_TRADES, spacing: 60));

    DB::enableQueryLog();

    // The calculators are proven above; this run proves the source: the pages, not the table.
    [$analytics, $growth] = peakGrowthOf(static fn (int $count) => TradingAnalytics::calculate(
        DB::table('trades')->where('id', '<=', $count)->orderBy('close_time')->orderBy('id'),
        only: [RiskAdjustedReturns::class, MaxDrawdown::class, Streaks::class],
        chunk: 500,
    ));

    $selects = array_column(DB::getQueryLog(), 'query');

    expect($analytics->riskAdjustedReturns?->sampleSize)->toBeGreaterThan(40_000)
        ->and($growth)->toBeLessThan(MEMORY_BOUND_BYTES)
        // 2 pages for the warm-up run, then 100 full pages and the empty one that ends it.
        ->and($selects)->toHaveCount(2 + 101)
        ->each->toContain('limit 500');
})->skip(coverageInstrumentationIsActive(), SKIPPED_UNDER_COVERAGE);
