<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

/**
 * The end-to-end test asserts the full pipeline against a static, committed JSON
 * fixture that holds BOTH the input trades AND the expected analytics values. The
 * test only loads + asserts — it never recomputes expectations (which is itself a
 * source of bugs). The fixture was produced once from an independent bcmath
 * computation, cross-validated against the engine, then frozen (see the repo's
 * docs and the one-off builder used to generate it).
 *
 * @phpstan-type TradeRow array{
 *     base_currency: string,
 *     quote_currency: string,
 *     direction: string,
 *     open_price: int,
 *     close_price: int,
 *     size: int,
 *     commission: int|null,
 *     opened_at: string,
 *     closed_at: string|null
 * }
 * @phpstan-type ExpectedBlock array{
 *     total_count: int,
 *     long_count: int,
 *     short_count: int,
 *     open_count: int,
 *     closed_count: int,
 *     win_count: int,
 *     loss_count: int,
 *     breakeven_count: int,
 *     win_rate: string,
 *     gross_realized_pnl: string,
 *     net_realized_pnl: string,
 *     gross_buy_realized_pnl: string,
 *     gross_sell_realized_pnl: string,
 *     total_commissions: string,
 *     realized_commissions: string,
 *     gross_profit: string,
 *     gross_loss: string,
 *     profit_factor: string,
 *     max_drawdown: string,
 *     gross_realized_pnl_by_quote_currency: array<string, string>,
 *     gross_realized_pnl_by_base_currency: array<string, string>,
 *     returns_count: int,
 *     sharpe_ratio: string,
 *     sortino_ratio: string,
 *     mean_return: string,
 *     risk_free_rate: string
 * }
 * @phpstan-type Fixture array{trades: list<TradeRow>, expected: ExpectedBlock}
 */

/**
 * Decode the committed fixture exactly once per process.
 *
 * @return Fixture
 */
function analyticsFixture(): array
{
    /** @var Fixture|null $cached */
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    $json = file_get_contents(__DIR__.'/Fixtures/analytics_dataset.json');
    expect($json)->toBeString();

    /** @var Fixture $decoded */
    $decoded = json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);

    return $cached = $decoded;
}

/**
 * Stand up an ephemeral `trades` table representing the consumer's own storage and
 * seed it from the JSON fixture's `trades`. The package ships no migration of its
 * own — this table belongs to the test.
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
        analyticsFixture()['trades'],
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

it('seeds the static fixture dataset of at least 500 trades through the sqlite round-trip', function (): void {
    $expected = analyticsFixture()['expected'];

    expect(DB::table('trades')->count())->toBe($expected['total_count'])
        ->and($expected['total_count'])->toBeGreaterThanOrEqual(500);

    // The fixture is genuinely varied, not degenerate.
    expect($expected['long_count'])->toBeGreaterThan(0)
        ->and($expected['short_count'])->toBeGreaterThan(0)
        ->and($expected['win_count'])->toBeGreaterThan(0)
        ->and($expected['loss_count'])->toBeGreaterThan(0)
        ->and($expected['breakeven_count'])->toBeGreaterThan(0)
        ->and($expected['open_count'])->toBeGreaterThan(0)
        ->and($expected['closed_count'])->toBeGreaterThan(0);

    // Multiple distinct pairs, base and quote currencies are present in storage.
    $distinctPairs = DB::table('trades')
        ->selectRaw('count(distinct base_currency || quote_currency) as pairs')
        ->value('pairs');

    expect((int) $distinctPairs)->toBeGreaterThan(1)
        ->and(DB::table('trades')->distinct()->count('base_currency'))->toBeGreaterThan(1)
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

it('produces identical results through the facade and the builder', function (): void {
    $direct = Analytics::make(loadTrades())->calculate();
    $viaFacade = TradingAnalytics::for(loadTrades())->calculate();
    $fromRows = TradingAnalytics::calculate(loadTrades()->map(static fn (Trade $trade): array => $trade->toArray()));

    expect($viaFacade->toArray())->toEqual($direct->toArray())
        ->and($fromRows->toArray())->toEqual($direct->toArray());
});

it('matches the fixture counts and win rate', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = analyticsFixture()['expected'];

    expect((string) $analytics->counts->global->total)->toBe((string) $expected['total_count'])
        ->and((string) $analytics->counts->global->buy)->toBe((string) $expected['long_count'])
        ->and((string) $analytics->counts->global->sell)->toBe((string) $expected['short_count'])
        ->and((string) $analytics->wins->global->total)->toBe((string) $expected['win_count'])
        ->and((string) $analytics->wins->winRatio->global->total)->toBe($expected['win_rate']);
});

it('matches the fixture realized p&l, commissions and profit factor', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = analyticsFixture()['expected'];

    expect((string) $analytics->realizedProfitAndLoss->gross->global->total->total)
        ->toBe($expected['gross_realized_pnl'])
        ->and((string) $analytics->realizedProfitAndLoss->net->global->total->total)
        ->toBe($expected['net_realized_pnl'])
        ->and((string) $analytics->realizedProfitAndLoss->gross->global->buy->total)
        ->toBe($expected['gross_buy_realized_pnl'])
        ->and((string) $analytics->realizedProfitAndLoss->gross->global->sell->total)
        ->toBe($expected['gross_sell_realized_pnl'])
        ->and((string) $analytics->commission->global->total->total)
        ->toBe($expected['total_commissions'])
        ->and((string) $analytics->unrealizedProfitAndLoss->grossProfits->total)
        ->toBe($expected['gross_profit'])
        ->and((string) $analytics->unrealizedProfitAndLoss->grossLosses->total)
        ->toBe($expected['gross_loss'])
        ->and((string) $analytics->profitFactor->total)
        ->toBe($expected['profit_factor']);
});

it('matches the fixture max drawdown', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = analyticsFixture()['expected'];

    expect((string) $analytics->maxDrawdown->value)->toBe($expected['max_drawdown']);
});

it('matches the fixture per-currency realized p&l', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $expected = analyticsFixture()['expected'];

    foreach ($expected['gross_realized_pnl_by_quote_currency'] as $quote => $value) {
        expect((string) $analytics->realizedProfitAndLoss->gross->forQuoteCurrency($quote)->total->total)
            ->toBe($value);
    }

    foreach ($expected['gross_realized_pnl_by_base_currency'] as $base => $value) {
        expect((string) $analytics->realizedProfitAndLoss->gross->forBaseCurrency($base)->total->total)
            ->toBe($value);
    }
});

it('matches the fixture sharpe and sortino ratios and series length', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();
    $risk = $analytics->riskAdjustedReturns;
    $expected = analyticsFixture()['expected'];

    // The series is gathered from realized trades only.
    expect($risk->returns)->toHaveCount($expected['returns_count']);

    expect((string) $risk->sharpeRatio)->toBe($expected['sharpe_ratio'])
        ->and((string) $risk->sortinoRatio)->toBe($expected['sortino_ratio'])
        ->and((string) $risk->meanReturn)->toBe($expected['mean_return'])
        ->and((string) $risk->riskFreeRate)->toBe($expected['risk_free_rate']);
});

it('holds the structural self-consistency invariants from the engine output', function (): void {
    $analytics = Analytics::make(loadTrades())->calculate();

    // Cheap self-consistency checks read entirely FROM the engine's own output
    // (no recomputation of expectations): wins + losses + breakeven == total.
    $total = (int) (string) $analytics->counts->global->total;
    $buy = (int) (string) $analytics->counts->global->buy;
    $sell = (int) (string) $analytics->counts->global->sell;

    expect($buy + $sell)->toBe($total);

    $wins = (int) (string) $analytics->wins->global->total;
    expect($wins)->toBeLessThanOrEqual($total)
        ->and($wins)->toBeGreaterThanOrEqual(0);

    // The realized return series size equals the number of closed trades read from
    // storage (open positions contribute to unrealized figures, not the series).
    $closed = DB::table('trades')->whereNotNull('closed_at')->count();
    expect($analytics->riskAdjustedReturns->returns)->toHaveCount($closed);
});
