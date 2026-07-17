<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests;

use Illuminate\Support\ServiceProvider;
use RoundlyConsulting\Testing\PackageTestCase;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsServiceProvider;

abstract class TestCase extends PackageTestCase
{
    /**
     * Every provider trading-analytics needs. `enums-for-laravel` is a hard `require` but
     * ships no provider (it is a helpers-only package), so the list is genuinely one
     * entry — not an omission.
     *
     * @return list<class-string<ServiceProvider>>
     */
    protected function packageProviders(): array
    {
        return [TradingAnalyticsServiceProvider::class];
    }

    /**
     * No `migrationSources()`: this package ships no migrations and never opens a
     * database connection — it is a pure calculation engine over an in-memory
     * LazyCollection of trades. That is also why the row carries no pgsql leg.
     *
     * The old `getEnvironmentSetUp()` set `database.default` to 'testing' and nothing
     * else; `PackageTestCase` does that (and the rest of the driver wiring) itself.
     */
}
