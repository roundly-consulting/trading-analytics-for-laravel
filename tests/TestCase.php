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
     * No `migrationSources()`: this package ships no migrations. It does read the host's
     * database, though — a query builder, Eloquent builder or relation passed as a trade
     * source is paged with `lazy()` — so the suites build a host `trades` table by hand
     * (tests/Support/TradesTable.php) and the workflow runs them on Postgres as well.
     *
     * The old `getEnvironmentSetUp()` set `database.default` to 'testing' and nothing
     * else; `PackageTestCase` does that (and the rest of the driver wiring) itself.
     */
}
