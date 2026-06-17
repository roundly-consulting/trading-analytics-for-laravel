<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @return array<int, class-string> */
    protected function getPackageProviders($app): array
    {
        return [TradingAnalyticsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
