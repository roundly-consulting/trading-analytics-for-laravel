<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Acme\LaravelPackageTools\Package;
use Acme\LaravelPackageTools\PackageServiceProvider;

final class TradingAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('trading-analytics')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('trading-analytics', fn (): AnalyticsFactory => new AnalyticsFactory);
    }
}
