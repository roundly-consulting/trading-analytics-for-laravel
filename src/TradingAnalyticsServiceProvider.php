<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Illuminate\Support\ServiceProvider;

final class TradingAnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/trading-analytics.php', 'trading-analytics');

        $this->app->singleton('trading-analytics', fn (): AnalyticsFactory => new AnalyticsFactory);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/trading-analytics.php' => config_path('trading-analytics.php'),
            ], 'trading-analytics-config');
        }
    }
}
