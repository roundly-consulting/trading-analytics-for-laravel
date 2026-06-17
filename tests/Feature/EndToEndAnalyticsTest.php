<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Tests\Feature\Fixtures\ExpectedAnalytics;
use RoundlyConsulting\TradingAnalytics\Tests\Feature\Fixtures\TradeDatasetGenerator;

/**
 * The size of the ephemeral consumer dataset. Deterministically generated, so a
 * larger number simply means a longer (still reproducible) curve.
 */
const DATASET_SIZE = 600;

/**
 * Stand up an ephemeral `trades` table representing the consumer's own storage.
 * The package ships no migration of its own — this table belongs to the test.
 */
beforeEach(function (): void {
    Schema::create('trades', function (Blueprint $table): void {
        $table->id();
        $table->string('base_currency');
        $table->string('quote_currency');
        $table->string('direction');
        $table->integer('open_price');
        $table->integer('close_price');
        $table->integer('size');
        $table->integer('commission')->nullable();
        $table->dateTime('opened_at');
        $table->dateTime('closed_at')->nullable();
    });

    // Batch insert the deterministic dataset (no Eloquent model required — this is
    // the consumer's table, ingested through the package's array boundary).
    $rows = array_map(
        static fn (array $row): array => [
            'base_currency' => $row['base_currency'],
            'quote_currency' => $row['quote_currency'],
            'direction' => $row['direction'],
            'open_price' => $row['open_price'],
            'close_price' => $row['close_price'],
            'size' => $row['size'],
            'commission' => $row['commission'],
            'opened_at' => $row['opened_at'],
            'closed_at' => $row['closed_at'],
        ],
        TradeDatasetGenerator::rows(DATASET_SIZE),
    );

    foreach (array_chunk($rows, 200) as $chunk) {
        DB::table('trades')->insert($chunk);
    }
});

afterEach(function (): void {
    Schema::dropIfExists('trades');
});

/**
 * Load the consumer's rows back out of the database and map them into the
 * package's {@see Trade} collection via its documented array boundary
 * ({@see Trade::collect()} / {@see Trade::fromArray()}).
 *
 * @return LazyCollection<int, Trade>
 */
function loadTrades(): LazyCollection
{
    return Trade::collect(
        DB::table('trades')->orderBy('id')->lazy()->map(static fn (object $row): array => [
            'base_currency' => $row->base_currency,
            'quote_currency' => $row->quote_currency,
            'open_price' => $row->open_price,
            'close_price' => $row->close_price,
            'size' => $row->size,
            'direction' => $row->direction,
            'open_time' => $row->opened_at,
            'commission' => $row->commission,
            'close_time' => $row->closed_at,
        ]),
    );
}

/** @return list<array<string, mixed>> */
function datasetRows(): array
{
    return TradeDatasetGenerator::rows(DATASET_SIZE);
}

it('seeds a deterministic, varied dataset of at least 500 trades', function (): void {
    expect(DB::table('trades')->count())->toBe(DATASET_SIZE)
        ->and(DATASET_SIZE)->toBeGreaterThanOrEqual(500);

    // The dataset must be genuinely varied, not degenerate.
    $expected = ExpectedAnalytics::fromRows(datasetRows());

    expect($expected->longCount)->toBeGreaterThan(0)
        ->and($expected->shortCount)->toBeGreaterThan(0)
        ->and($expected->winCount)->toBeGreaterThan(0)
        ->and($expected->lossCount)->toBeGreaterThan(0)
        ->and($expected->breakevenCount)->toBeGreaterThan(0)
        ->and($expected->openCount)->toBeGreaterThan(0)
        ->and($expected->closedCount)->toBeGreaterThan(0);

    // Multiple distinct pairs, base and quote currencies are present.
    $distinctPairs = DB::table('trades')
        ->selectRaw('count(distinct base_currency || quote_currency) as pairs')
        ->value('pairs');
    expect((int) $distinctPairs)->toBeGreaterThan(1);

    expect(DB::table('trades')->distinct()->count('base_currency'))->toBeGreaterThan(1)
        ->and(DB::table('trades')->distinct()->count('quote_currency'))->toBeGreaterThan(1)
        ->and(DB::table('trades')->whereNotNull('commission')->where('commission', '>', 0)->count())->toBeGreaterThan(0)
        ->and(DB::table('trades')->whereNull('commission')->count())->toBeGreaterThan(0);
});

it('runs the full pipeline and serializes without error', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();

    expect($analytics->hasBeenCalculated())->toBeTrue();

    // Every calculator produced a result.
    expect($analytics->counts)->not->toBeNull()
        ->and($analytics->wins)->not->toBeNull()
        ->and($analytics->realizedProfitAndLoss)->not->toBeNull()
        ->and($analytics->unrealizedProfitAndLoss)->not->toBeNull()
        ->and($analytics->commission)->not->toBeNull()
        ->and($analytics->profitFactor)->not->toBeNull()
        ->and($analytics->maxDrawdown)->not->toBeNull()
        ->and($analytics->riskAdjustedReturns)->not->toBeNull();

    // The whole result tree serializes cleanly through all three contracts.
    $array = $analytics->toArray();
    expect($array)->toBeArray()->not->toBeEmpty();

    $json = json_encode($analytics->jsonSerialize(), JSON_THROW_ON_ERROR);
    expect($json)->toBeString()
        ->and(json_decode($json, true))->toBeArray();

    expect($analytics->toJson())->toBeString();
});

it('produces identical results through the facade', function (): void {
    $direct = Analytics::make(loadTrades())->calculate();
    $viaFacade = TradingAnalytics::make(loadTrades())->calculate();

    expect($viaFacade->toArray())->toEqual($direct->toArray());
});

it('matches independently re-computed counts and win rate', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = ExpectedAnalytics::fromRows(datasetRows());

    expect((string) $analytics->counts->global->total)->toBe((string) $expected->totalCount)
        ->and((string) $analytics->counts->global->buy)->toBe((string) $expected->longCount)
        ->and((string) $analytics->counts->global->sell)->toBe((string) $expected->shortCount);

    // Wins are gross-P&L winners across every trade.
    expect((string) $analytics->wins->global->total)->toBe((string) $expected->winCount);

    // Win rate (wins / total) at scale 2.
    expect((string) $analytics->wins->winRatio->global->total)->toBe($expected->winRate);
});

it('matches independently re-computed realized p&l, commissions and profit factor', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = ExpectedAnalytics::fromRows(datasetRows());

    // Gross & net realized P&L.
    expect((string) $analytics->realizedProfitAndLoss->gross->global->total->total)
        ->toBe($expected->grossRealizedPnl)
        ->and((string) $analytics->realizedProfitAndLoss->net->global->total->total)
        ->toBe($expected->netRealizedPnl);

    // Directional split of gross realized P&L.
    expect((string) $analytics->realizedProfitAndLoss->gross->global->buy->total)
        ->toBe($expected->grossBuyRealizedPnl)
        ->and((string) $analytics->realizedProfitAndLoss->gross->global->sell->total)
        ->toBe($expected->grossSellRealizedPnl);

    // Total commissions.
    expect((string) $analytics->commission->global->total->total)
        ->toBe($expected->totalCommissions);

    // Gross profit / gross loss / profit factor (unrealized basis, per the engine).
    expect((string) $analytics->unrealizedProfitAndLoss->grossProfits->total)
        ->toBe($expected->grossProfit)
        ->and((string) $analytics->unrealizedProfitAndLoss->grossLosses->total)
        ->toBe($expected->grossLoss)
        ->and((string) $analytics->profitFactor->total)
        ->toBe($expected->profitFactor);
});

it('matches independently re-computed max drawdown', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = ExpectedAnalytics::fromRows(datasetRows());

    expect((string) $analytics->maxDrawdown->value)->toBe($expected->maxDrawdown)
        // Guard against a degenerate (always-rising) equity curve making the
        // assertion vacuous — this dataset must actually draw down.
        ->and(bccomp($expected->maxDrawdown, '0', ExpectedAnalytics::SCALE))->toBeGreaterThan(0);
});

it('matches independently re-computed per-currency realized p&l', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = ExpectedAnalytics::fromRows(datasetRows());

    foreach ($expected->grossRealizedPnlByQuoteCurrency as $quote => $value) {
        expect((string) $analytics->realizedProfitAndLoss->gross->forQuoteCurrency($quote)->total->total)
            ->toBe($value);
    }

    foreach ($expected->grossRealizedPnlByBaseCurrency as $base => $value) {
        expect((string) $analytics->realizedProfitAndLoss->gross->forBaseCurrency($base)->total->total)
            ->toBe($value);
    }
});

it('produces present, finite, correctly-scaled sharpe and sortino ratios', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $risk = $analytics->riskAdjustedReturns;

    // The series is gathered from realized trades only.
    $expected = ExpectedAnalytics::fromRows(datasetRows());
    expect($risk->returns)->toHaveCount($expected->closedCount);

    // Ratios are present, finite and formatted at scale 4.
    foreach ([(string) $risk->sharpeRatio, (string) $risk->sortinoRatio] as $ratio) {
        expect(is_numeric($ratio))->toBeTrue()
            ->and(is_finite((float) $ratio))->toBeTrue()
            ->and($ratio)->toMatch('/^-?\d+\.\d{4}$/');
    }

    // Sharpe sign follows the mean excess return: positive mean => positive Sharpe.
    $meanSign = bccomp((string) $risk->meanReturn, (string) $risk->riskFreeRate, 10);
    $sharpeSign = bccomp((string) $risk->sharpeRatio, '0', 4);

    if ($meanSign > 0) {
        expect($sharpeSign)->toBeGreaterThanOrEqual(0);
    } elseif ($meanSign < 0) {
        expect($sharpeSign)->toBeLessThanOrEqual(0);
    }
});

it('holds the structural integration invariants', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = ExpectedAnalytics::fromRows(datasetRows());

    // wins + losses + breakeven == total
    expect($expected->winCount + $expected->lossCount + $expected->breakevenCount)
        ->toBe($expected->totalCount);

    // open + closed == total, long + short == total
    expect($expected->openCount + $expected->closedCount)->toBe($expected->totalCount)
        ->and($expected->longCount + $expected->shortCount)->toBe($expected->totalCount);

    // The engine's own counts agree with the totals.
    expect((int) (string) $analytics->counts->global->total)->toBe($expected->totalCount)
        ->and((int) (string) $analytics->counts->global->buy + (int) (string) $analytics->counts->global->sell)
        ->toBe($expected->totalCount);

    // gross realized P&L - realized commissions == net realized P&L.
    $grossMinusCommissions = bcsub($expected->grossRealizedPnl, $expected->realizedCommissions, ExpectedAnalytics::SCALE);
    expect($grossMinusCommissions)->toBe($expected->netRealizedPnl)
        ->and((string) $analytics->realizedProfitAndLoss->net->global->total->total)
        ->toBe($grossMinusCommissions);

    // Directional gross realized P&L sums back to the global gross realized P&L.
    $buySell = bcadd($expected->grossBuyRealizedPnl, $expected->grossSellRealizedPnl, ExpectedAnalytics::SCALE);
    expect($buySell)->toBe($expected->grossRealizedPnl);

    // Per-quote-currency gross realized P&L sums back to the global figure.
    $byQuoteSum = array_reduce(
        $expected->grossRealizedPnlByQuoteCurrency,
        static fn (string $carry, string $value): string => bcadd($carry, $value, ExpectedAnalytics::SCALE),
        '0.0000000000',
    );
    expect($byQuoteSum)->toBe($expected->grossRealizedPnl);

    // Open positions contribute to the unrealized figures, not the realized ones:
    // the realized series size equals the number of closed trades.
    expect($analytics->riskAdjustedReturns->returns)->toHaveCount($expected->closedCount);
});
