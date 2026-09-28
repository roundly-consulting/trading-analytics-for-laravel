<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsManager;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsServiceProvider;

it('merges the package config', function (): void {
    expect(config('trading-analytics.scale'))->toBe(10)
        ->and(config('trading-analytics.win_rate_period'))->toBe('daily');
});

it('binds the manager as a singleton', function (): void {
    expect(app(TradingAnalyticsManager::class))->toBe(app(TradingAnalyticsManager::class));
});

it('publishes the config under the trading-analytics-config tag', function (): void {
    $paths = ServiceProvider::pathsToPublish(TradingAnalyticsServiceProvider::class, 'trading-analytics-config');

    expect($paths)->toHaveCount(1)
        ->and(array_key_first($paths))->toEndWith('config/trading-analytics.php')
        ->and(array_values($paths)[0])->toEndWith('config/trading-analytics.php');
});

it('contributes a section to the about command', function (): void {
    $this->artisan('about', ['--only' => 'trading-analytics'])
        ->expectsOutputToContain('10')
        ->assertSuccessful();

    $this->artisan('about', ['--only' => 'trading-analytics'])
        ->expectsOutputToContain('daily')
        ->assertSuccessful();
});

it('reports an unset scale and an unrecognised period to the about command', function (): void {
    config()->set('trading-analytics.scale', null);
    config()->set('trading-analytics.win_rate_period', 'hourly');

    $this->artisan('about', ['--only' => 'trading-analytics'])
        ->expectsOutputToContain('DEFAULT')
        ->assertSuccessful();

    $this->artisan('about', ['--only' => 'trading-analytics'])
        ->expectsOutputToContain('fallback')
        ->assertSuccessful();
});
