<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\LazyCollection;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Counts;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\CumulativeReturn;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Expectancy;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\MaxDrawdown;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitFactor;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\RiskAdjustedReturns;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\RiskRewardRatio;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Streaks;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradesDuration;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingCommissions;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingFrequency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingValue;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingVolume;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\WinRateByPeriod;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Wins;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Period;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnknownCalculatorException;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\Traits\HasScale;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/**
 * Entry point for the engine. Open for extension: subclass it to register custom
 * calculators or override the per-trade / after-trades hooks.
 *
 * Subclasses must keep a constructor signature compatible with this one so the
 * late-static make()/for() builders stay safe.
 *
 * @implements Arrayable<string, mixed>
 *
 * @phpstan-consistent-constructor
 */
class Analytics implements Arrayable, Jsonable, JsonSerializable
{
    use HasScale;
    use SerializesToJson;

    public ?Counts $counts = null;

    public ?Wins $wins = null;

    public ?TradingVolume $volume = null;

    public ?TradingValue $value = null;

    public ?ProfitAndLoss $unrealizedProfitAndLoss = null;

    public ?ProfitAndLoss $realizedProfitAndLoss = null;

    public ?ProfitFactor $profitFactor = null;

    public ?TradingCommissions $commission = null;

    public ?CumulativeReturn $cumulativeReturn = null;

    public ?TradingFrequency $frequency = null;

    public ?TradesDuration $duration = null;

    public ?Streaks $streaks = null;

    public ?Expectancy $expectancy = null;

    public ?RiskRewardRatio $riskRewardRatio = null;

    public ?WinRateByPeriod $winRateByPeriod = null;

    public ?MaxDrawdown $maxDrawdown = null;

    public ?RiskAdjustedReturns $riskAdjustedReturns = null;

    /** @var list<class-string<AnalyticsInterface>> */
    protected array $defaultCalculators = [
        Analytics\Counts::class,
        Analytics\TradingVolume::class,
        Analytics\TradingValue::class,
        Analytics\Commissions::class,
        Analytics\UnrealizedGrossProfitAndLoss::class,
        Analytics\UnrealizedNetProfitAndLoss::class,
        Analytics\RealizedGrossProfitAndLoss::class,
        Analytics\RealizedNetProfitAndLoss::class,
        Analytics\Wins::class,
        Analytics\ProfitFactor::class,
        Analytics\GrossCumulativeReturn::class,
        Analytics\NetCumulativeReturn::class,
        Analytics\TradingFrequency::class,
        Analytics\TradesDuration::class,
        Analytics\Streaks::class,
        Analytics\Expectancy::class,
        Analytics\RiskRewardRatio::class,
        Analytics\WinRateByPeriod::class,
        Analytics\MaxDrawdown::class,
        Analytics\RiskAdjustedReturns::class,
    ];

    /** @var list<class-string<AnalyticsInterface>> */
    protected array $calculators;

    /**
     * Calculators that depend on aggregates produced by other calculators, so
     * selecting one with {@see only()} also pulls its dependencies in.
     *
     * @var array<class-string<AnalyticsInterface>, list<class-string<AnalyticsInterface>>>
     */
    protected array $dependencies = [
        Analytics\Wins::class => [Analytics\Counts::class],
        Analytics\TradingFrequency::class => [Analytics\Counts::class],
        Analytics\ProfitFactor::class => [Analytics\UnrealizedGrossProfitAndLoss::class],
        Analytics\GrossCumulativeReturn::class => [Analytics\Counts::class],
        Analytics\NetCumulativeReturn::class => [Analytics\Counts::class],
        Analytics\Expectancy::class => [Analytics\RealizedGrossProfitAndLoss::class],
        Analytics\RiskRewardRatio::class => [Analytics\RealizedGrossProfitAndLoss::class],
    ];

    protected ?Closure $onEachTrade = null;

    protected ?Closure $afterEachTrades = null;

    protected Period $winRatePeriod = Period::DAILY;

    protected bool $hasBeenCalculated = false;

    /** @param LazyCollection<int, Trade> $trades */
    public function __construct(protected LazyCollection $trades)
    {
        $this->calculators = $this->defaultCalculators;

        $this->scale = $this->defaultScale();
        $this->winRatePeriod = $this->defaultWinRatePeriod();
    }

    /**
     * The default bcmath scale: the configured value when a Laravel config
     * repository is bound, otherwise the library's built-in default so the
     * engine still works outside a booted app.
     */
    protected function defaultScale(): int
    {
        $configured = $this->configuredValue('trading-analytics.scale');

        return is_numeric($configured) ? (int) $configured : $this->scale;
    }

    /**
     * The default win-rate bucketing period: the configured value when bound
     * and recognised, otherwise the built-in default. An unrecognised value
     * falls back rather than throwing at construction.
     */
    protected function defaultWinRatePeriod(): Period
    {
        $configured = $this->configuredValue('trading-analytics.win_rate_period');

        if (is_string($configured) && ($period = Period::tryFrom($configured)) !== null) {
            return $period;
        }

        return $this->winRatePeriod;
    }

    /**
     * Read a config value only when a Laravel container with a bound config
     * repository is available, so the package never assumes a booted app.
     */
    protected function configuredValue(string $key): mixed
    {
        if (! function_exists('app') || ! app()->bound('config')) {
            return null;
        }

        return config($key);
    }

    /**
     * @param  LazyCollection<int, Trade>  $trades
     */
    public static function make(LazyCollection $trades): static
    {
        return new static($trades);
    }

    /**
     * @param  LazyCollection<int, Trade>  $trades
     */
    public static function for(LazyCollection $trades): static
    {
        return new static($trades);
    }

    /**
     * Restrict the run to the given calculators (plus their dependencies).
     *
     * @param  list<class-string<AnalyticsInterface>>  $calculators
     */
    public function only(array $calculators): static
    {
        $this->calculators = $this->resolveCalculators($calculators);

        return $this;
    }

    /**
     * Run every calculator except the given ones.
     *
     * @param  list<class-string<AnalyticsInterface>>  $calculators
     */
    public function except(array $calculators): static
    {
        foreach ($calculators as $calculator) {
            $this->assertIsCalculator($calculator);
        }

        $this->calculators = array_values(array_filter(
            $this->defaultCalculators,
            static fn (string $calculator): bool => ! in_array($calculator, $calculators, true),
        ));

        return $this;
    }

    public function usingWinRatePeriod(Period $period): static
    {
        $this->winRatePeriod = $period;

        return $this;
    }

    public function onEachTrade(Closure $closure): static
    {
        $this->onEachTrade = $closure;

        return $this;
    }

    public function afterTrades(Closure $closure): static
    {
        $this->afterEachTrades = $closure;

        return $this;
    }

    public function calculate(): static
    {
        $this->initializeAnalyticsResults();

        // Go through each trade once and run every active calculator's per-trade hook.
        foreach ($this->trades as $trade) {
            foreach ($this->calculators as $calculator) {
                $this->calculatePerTrade($calculator, $trade);
            }
        }

        foreach ($this->calculators as $calculator) {
            $this->calculateAfterTrades($calculator);
        }

        $this->hasBeenCalculated = true;

        return $this;
    }

    public function hasBeenCalculated(): bool
    {
        return $this->hasBeenCalculated;
    }

    /**
     * The calculator class-strings this engine runs, in canonical order — the
     * values {@see only()} and {@see except()} accept.
     *
     * @return list<class-string<AnalyticsInterface>>
     */
    public function metrics(): array
    {
        return $this->defaultCalculators;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        if (! $this->hasBeenCalculated) {
            return [];
        }

        return array_filter([
            'counts' => $this->counts?->toArray(),
            'wins' => $this->wins?->toArray(),
            'volume' => $this->volume?->toArray(),
            'value' => $this->value?->toArray(),
            'commission' => $this->commission?->toArray(),
            'profit_and_loss' => array_filter([
                'unrealized' => $this->unrealizedProfitAndLoss?->toArray(),
                'realized' => $this->realizedProfitAndLoss?->toArray(),
            ], static fn (mixed $value): bool => $value !== null) ?: null,
            'profit_factor' => $this->profitFactor?->toArray(),
            'cumulative_return' => $this->cumulativeReturn?->toArray(),
            'frequency' => $this->frequency?->toArray(),
            'duration' => $this->duration?->toArray(),
            'streaks' => $this->streaks?->toArray(),
            'expectancy' => $this->expectancy?->toArray(),
            'risk_reward_ratio' => $this->riskRewardRatio?->toArray(),
            'win_rate_by_period' => $this->winRateByPeriod?->toArray(),
            'max_drawdown' => $this->maxDrawdown?->toArray(),
            'risk_adjusted_returns' => $this->riskAdjustedReturns?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }

    protected function initializeAnalyticsResults(): void
    {
        $active = $this->calculators;

        $initializers = [
            Analytics\Counts::class => fn () => $this->counts = new Counts,
            Analytics\Wins::class => fn () => $this->wins = new Wins,
            Analytics\TradingVolume::class => fn () => $this->volume = new TradingVolume($this->scale),
            Analytics\TradingValue::class => fn () => $this->value = new TradingValue($this->scale),
            Analytics\Commissions::class => fn () => $this->commission = new TradingCommissions($this->scale),
            Analytics\ProfitFactor::class => fn () => $this->profitFactor = new ProfitFactor,
            Analytics\TradingFrequency::class => fn () => $this->frequency = new TradingFrequency,
            Analytics\TradesDuration::class => fn () => $this->duration = new TradesDuration,
            Analytics\Streaks::class => fn () => $this->streaks = new Streaks,
            Analytics\Expectancy::class => fn () => $this->expectancy = new Expectancy($this->scale),
            Analytics\RiskRewardRatio::class => fn () => $this->riskRewardRatio = new RiskRewardRatio,
            Analytics\WinRateByPeriod::class => fn () => $this->winRateByPeriod = new WinRateByPeriod($this->winRatePeriod),
            Analytics\MaxDrawdown::class => fn () => $this->maxDrawdown = new MaxDrawdown($this->scale),
            Analytics\RiskAdjustedReturns::class => fn () => $this->riskAdjustedReturns = new RiskAdjustedReturns,
        ];

        foreach ($initializers as $calculator => $initializer) {
            if (in_array($calculator, $active, true)) {
                $initializer();
            }
        }

        // Unrealized and realized P&L are each written by a gross and a net calculator.
        if (array_intersect([Analytics\UnrealizedGrossProfitAndLoss::class, Analytics\UnrealizedNetProfitAndLoss::class], $active) !== []) {
            $this->unrealizedProfitAndLoss = new ProfitAndLoss($this->scale);
        }

        if (array_intersect([Analytics\RealizedGrossProfitAndLoss::class, Analytics\RealizedNetProfitAndLoss::class], $active) !== []) {
            $this->realizedProfitAndLoss = new ProfitAndLoss($this->scale);
        }

        if (array_intersect([Analytics\GrossCumulativeReturn::class, Analytics\NetCumulativeReturn::class], $active) !== []) {
            $this->cumulativeReturn = new CumulativeReturn;
        }
    }

    /** @param class-string<AnalyticsInterface> $calculator */
    protected function calculatePerTrade(string $calculator, Trade $trade): void
    {
        if ($this->onEachTrade !== null) {
            ($this->onEachTrade)($this, $calculator, $trade);
        } else {
            $calculator::calculatePerTrade($this, $trade);
        }
    }

    /** @param class-string<AnalyticsInterface> $calculator */
    protected function calculateAfterTrades(string $calculator): void
    {
        if ($this->afterEachTrades !== null) {
            ($this->afterEachTrades)($this, $calculator);
        } else {
            $calculator::calculateAfterTrades($this);
        }
    }

    /**
     * Validate and expand a requested calculator set with its dependencies,
     * preserving the canonical run order.
     *
     * @param  list<class-string<AnalyticsInterface>>  $requested
     * @return list<class-string<AnalyticsInterface>>
     */
    protected function resolveCalculators(array $requested): array
    {
        $wanted = [];

        foreach ($requested as $calculator) {
            $this->assertIsCalculator($calculator);

            $wanted[$calculator] = true;

            foreach ($this->dependencies[$calculator] ?? [] as $dependency) {
                $wanted[$dependency] = true;
            }
        }

        return array_values(array_filter(
            $this->defaultCalculators,
            static fn (string $calculator): bool => isset($wanted[$calculator]),
        ));
    }

    /** @param class-string<AnalyticsInterface> $calculator */
    protected function assertIsCalculator(string $calculator): void
    {
        if (! in_array($calculator, $this->defaultCalculators, true)) {
            throw UnknownCalculatorException::notACalculator($calculator);
        }
    }
}
