<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Closure;
use RoundlyConsulting\PackageToolkit\Exceptions\InvalidConfigurationException;
use RoundlyConsulting\PackageToolkit\Package;
use RoundlyConsulting\PackageToolkit\PackageServiceProvider;
use RoundlyConsulting\PackageToolkit\Support\Config;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

final class TradingAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('trading-analytics')
            ->hasConfigFile()
            ->contributesToAbout(static fn (): array => [
                'Decimal scale' => self::orInvalid(static fn (): string => (string) Config::integer('trading-analytics.scale', 10, min: 0)),
                'Win-rate period' => self::orInvalid(static fn (): string => Config::enum('trading-analytics.win_rate_period', Period::class, Period::DAILY)->value),
            ]);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(TradingAnalyticsManager::class);
    }

    /**
     * A strict read for an `about` row, mirroring the engine: a broken value renders as
     * INVALID (the engine throws on it) and `about` keeps working.
     *
     * @param  Closure(): string  $read
     */
    private static function orInvalid(Closure $read): string
    {
        try {
            return $read();
        } catch (InvalidConfigurationException) {
            return 'INVALID';
        }
    }
}
