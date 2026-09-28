<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Tests\Support\RiskFreeRateAnalytics;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradeDatasets;

/**
 * Golden vectors, frozen from the engine BEFORE the risk-adjusted calculator stopped storing
 * the per-trade return series (it now folds each return into running sums). The rewrite must
 * reproduce the old figures exactly, so the fixture pins:
 *
 * - every field of `riskAdjustedReturns->toArray()`, verbatim; and
 * - the full `Analytics::toArray()`, as one SHA-256 per top-level section, so a mismatch
 *   names the metric that moved without committing megabytes of nested output.
 *
 * Never regenerate this file to make a red test green: a moved vector is a behaviour change.
 *
 * Re-frozen deliberately where a vector had frozen a bug. Each entry names the only sections
 * that moved; every other section of every vector held. The corrected figures are pinned by
 * hand, independently of the engine, in HandComputedMetricsTest.
 *
 * - `profit_and_loss`, `profit_factor`, `expectancy`, `risk_reward_ratio` — realized and
 *   unrealized profits/losses leaked into each other (every trade landed on both sides), and
 *   the profit factor read the unrealized side.
 * - `profit_and_loss`, `duration`, `commission`, `cumulative_return` — averages divided by every
 *   trade instead of the trades that fed them (realized over open + closed); a zero value
 *   (break-even trade, same-second trade, no commission) was taken for "unset" and overwritten
 *   as the highest / lowest; a cumulative return could never report a highest below 0; and a
 *   trade without a commission was skipped by the commission extremes (it now counts as 0).
 *
 * @phpstan-type GoldenVector array{risk_adjusted_returns: array<string, string|int>, analytics_sha256: array<string, string>}
 */

/** @return array<string, GoldenVector> */
function goldenVectors(): array
{
    /** @var array<string, GoldenVector> $vectors */
    $vectors = json_decode((string) file_get_contents(__DIR__.'/Fixtures/golden_vectors.json'), true, 512, JSON_THROW_ON_ERROR);

    return $vectors;
}

/** @return array<string, array{class-string<Analytics>, string}> */
function goldenCases(): array
{
    $cases = [];

    foreach (TradeDatasets::GOLDEN as $name) {
        $cases[$name] = [Analytics::class, $name];
    }

    // The same figures against a non-zero risk-free rate: the Sharpe excess and the
    // Sortino shortfall both move with it.
    foreach (['mixed-signs', 'zero-variance-loss', 'random-2500'] as $name) {
        $cases["{$name}@risk-free-rate"] = [RiskFreeRateAnalytics::class, $name];
    }

    return $cases;
}

it('covers every frozen vector and nothing else', function (): void {
    expect(array_keys(goldenVectors()))->toBe(array_keys(goldenCases()))
        ->and(goldenVectors())->toHaveCount(11);
});

it('reproduces the frozen analytics vector', function (string $case): void {
    [$engine, $dataset] = goldenCases()[$case];
    $expected = goldenVectors()[$case];

    $analytics = $engine::for(TradeDatasets::golden($dataset))->calculate();

    $sections = array_map(
        static fn (mixed $section): string => hash('sha256', json_encode($section, JSON_THROW_ON_ERROR)),
        $analytics->toArray(),
    );

    expect($analytics->riskAdjustedReturns?->toArray())->toBe($expected['risk_adjusted_returns'])
        ->and($sections)->toBe($expected['analytics_sha256']);
})->with(array_keys(goldenCases()));
