<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\AnalyticsFactory;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

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

it('still returns analytics from the base factory', function (LazyCollection $trades) {
    $factory = new AnalyticsFactory;

    expect($factory->make($trades))->toBeInstanceOf(Analytics::class);
})->with('empty-trades');
