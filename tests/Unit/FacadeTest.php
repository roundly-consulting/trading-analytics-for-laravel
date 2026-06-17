<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\AnalyticsFactory;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

it('builds an analytics instance through the facade', function () {
    $trades = new LazyCollection([]);

    expect(TradingAnalytics::make($trades))->toBeInstanceOf(Analytics::class)
        ->and(TradingAnalytics::for($trades))->toBeInstanceOf(Analytics::class);
});

it('resolves the factory from the container', function () {
    expect(app('trading-analytics'))->toBeInstanceOf(AnalyticsFactory::class);
});
