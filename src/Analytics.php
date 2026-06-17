<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics;

use Closure;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Counts;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\CumulativeReturn;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitFactor;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Streaks;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradesDuration;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingCommissions;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingFrequency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingValue;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingVolume;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Wins;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;
use RoundlyConsulting\TradingAnalytics\Traits\HasScale;

class Analytics
{
    use HasScale;

    public ?Counts $counts = null;

    public ?Wins $wins = null;

    public ?TradingVolume $volume = null;

    public ?TradingValue $value = null;

    public ?ProfitAndLoss $unrealizedProfitAndLoss = null;

    public ?ProfitAndLoss $realizedProfitAndLoss = null;

    public ?ProfitFactor $profitFactor = null;

    public ?TradingCommissions $comission = null;

    public ?CumulativeReturn $cumulativeReturn = null;

    public ?TradingFrequency $frequency = null;

    public ?TradesDuration $duration = null;

    public ?Streaks $streaks = null;

    /** @var list<class-string<AnalyticsInterface>> */
    protected array $calculators = [
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
    ];

    protected static ?Closure $calculatePerTradeUsing = null;

    protected static ?Closure $calculateAfterTradesUsing = null;

    protected bool $hasBeenCalculated = false;

    /** @param LazyCollection<int, Trade> $trades */
    public function __construct(protected LazyCollection $trades) {}

    public static function calculatePerTradeUsing(Closure $closure): void
    {
        static::$calculatePerTradeUsing = $closure;
    }

    public static function calculateAfterTradesUsing(Closure $closure): void
    {
        static::$calculateAfterTradesUsing = $closure;
    }

    public static function calculatePerTradesNormally(): void
    {
        static::$calculatePerTradeUsing = null;
    }

    public static function calculateAfterTradesNormally(): void
    {
        static::$calculateAfterTradesUsing = null;
    }

    public function calculate(): void
    {
        $this->initializeAnalyticsResults();

        // Go through each trade once and calculate all the analytics
        foreach ($this->trades as $trade) {
            foreach ($this->calculators as $calculator) {
                $this->calculatePerTrade($calculator, $trade);
            }
        }

        foreach ($this->calculators as $calculator) {
            $this->calculateAfterTrades($calculator);
        }

        $this->hasBeenCalculated = true;
    }

    public function hasBeenCalculated(): bool
    {
        return $this->hasBeenCalculated;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        if (! $this->hasBeenCalculated) {
            return [];
        }

        return [
            'counts' => $this->counts->toArray(),
            'wins' => $this->wins->toArray(),
            'volume' => $this->volume->toArray(),
            'value' => $this->value->toArray(),
            'comission' => $this->comission->toArray(),
            'profit_and_loss' => [
                'unrealized' => $this->unrealizedProfitAndLoss->toArray(),
                'realized' => $this->realizedProfitAndLoss->toArray(),
            ],
            'profit_factor' => $this->profitFactor->toArray(),
            'cumulative_return' => $this->cumulativeReturn->toArray(),
            'frequency' => $this->frequency->toArray(),
            'duration' => $this->duration->toArray(),
            'streaks' => $this->streaks->toArray(),
        ];
    }

    protected function initializeAnalyticsResults(): void
    {
        $this->counts = new Counts;
        $this->wins = new Wins;
        $this->volume = new TradingVolume($this->scale);
        $this->value = new TradingValue($this->scale);
        $this->comission = new TradingCommissions($this->scale);
        $this->unrealizedProfitAndLoss = new ProfitAndLoss($this->scale);
        $this->realizedProfitAndLoss = new ProfitAndLoss($this->scale);
        $this->profitFactor = new ProfitFactor;
        $this->cumulativeReturn = new CumulativeReturn;
        $this->frequency = new TradingFrequency;
        $this->duration = new TradesDuration;
        $this->streaks = new Streaks;
    }

    /** @param class-string<AnalyticsInterface> $calculator */
    protected function calculatePerTrade(string $calculator, Trade $trade): void
    {
        if (static::$calculatePerTradeUsing) {
            (static::$calculatePerTradeUsing)($this, $calculator, $trade);
        } else {
            $calculator::calculatePerTrade($this, $trade);
        }
    }

    /** @param class-string<AnalyticsInterface> $calculator */
    protected function calculateAfterTrades(string $calculator): void
    {
        if (static::$calculateAfterTradesUsing) {
            (static::$calculateAfterTradesUsing)($this, $calculator);
        } else {
            $calculator::calculateAfterTrades($this);
        }
    }
}
