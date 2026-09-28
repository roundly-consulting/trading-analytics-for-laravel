<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradeDatasets;

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
});
