<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use RoundlyConsulting\PackageToolkit\Package;
use RoundlyConsulting\PackageToolkit\PackageServiceProvider;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

final class TradingAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('trading-analytics')
            ->hasConfigFile()
            ->contributesToAbout(static function (): array {
                $scale = config('trading-analytics.scale');
                $period = config('trading-analytics.win_rate_period');

                return [
                    'Decimal scale' => is_numeric($scale) ? (string) (int) $scale : 'DEFAULT',
                    'Win-rate period' => self::winRatePeriod($period),
                ];
            });
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton('trading-analytics', fn (): AnalyticsFactory => new AnalyticsFactory);
    }

    /**
     * The configured bucketing period, mirroring the engine's own
     * fall-back-rather-than-throw handling of an unrecognised value.
     */
    private static function winRatePeriod(mixed $configured): string
    {
        if (is_string($configured) && ($period = Period::tryFrom($configured)) !== null) {
            return $period->value;
        }

        return Period::DAILY->value.' (fallback)';
    }
}
