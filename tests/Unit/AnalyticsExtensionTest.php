<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Enums\Period;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

/**
 * A consumer subclass used to prove the documented "subclass to extend" story:
 * make()/for() must return the called class, not the base Analytics.
 */
final class CustomAnalytics extends Analytics {}

it('returns the called class from make for a subclass', function (LazyCollection $trades) {
    expect(CustomAnalytics::make($trades))->toBeInstanceOf(CustomAnalytics::class);
})->with('empty-trades');

it('returns the called class from for for a subclass', function (LazyCollection $trades) {
    expect(CustomAnalytics::for($trades))->toBeInstanceOf(CustomAnalytics::class);
})->with('empty-trades');

it('keeps the subclass through a fluent setter chain', function (LazyCollection $trades) {
    expect(CustomAnalytics::make($trades)->scale(4)->usingWinRatePeriod(
        Period::WEEKLY,
    ))->toBeInstanceOf(CustomAnalytics::class);
})->with('empty-trades');

it('builds the base engine through the manager by default', function (LazyCollection $trades) {
    expect(TradingAnalytics::for($trades))->toBeInstanceOf(Analytics::class)
        ->not->toBeInstanceOf(CustomAnalytics::class);
})->with('empty-trades');

it('builds a subclass engine through the manager once configured', function (LazyCollection $trades) {
    TradingAnalytics::using(CustomAnalytics::class);

    expect(TradingAnalytics::for($trades))->toBeInstanceOf(CustomAnalytics::class)
        ->and(TradingAnalytics::calculate($trades))->toBeInstanceOf(CustomAnalytics::class)
        ->and(TradingAnalytics::engine())->toBe(CustomAnalytics::class);
})->with('empty-trades');
