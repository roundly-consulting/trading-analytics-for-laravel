<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/**
 * Variance-based risk-adjusted return ratios (Sharpe, Sortino).
 *
 * The per-trade return series is never stored: each realized return is folded into three
 * running sums as the single pass reaches it, and mean / deviation / ratios are derived from
 * those sums once the pass ends. Memory stays constant however long the trade history is.
 *
 * @implements Arrayable<string, mixed>
 */
final class RiskAdjustedReturns implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    /** The bcmath scale every return is folded in at. */
    public const int WORK_SCALE = 20;

    /**
     * Squares of WORK_SCALE values need twice the digits to stay exact; an exact Σr² is what
     * lets the deviation be derived from sums without drifting from the two-pass figure.
     */
    public const int SQUARE_SCALE = 2 * self::WORK_SCALE;

    /** How many realized returns have been folded in. */
    public int $sampleSize = 0;

    /** Σr: the sum of the realized returns. */
    public NumericValueAsString $sumOfReturns;

    /** Σr², exact at {@see SQUARE_SCALE}. */
    public NumericValueAsString $sumOfSquaredReturns;

    /** Σ min(r − risk-free rate, 0)²: the squared shortfalls behind the Sortino ratio. */
    public NumericValueAsString $sumOfSquaredShortfalls;

    public NumericValueAsString $meanReturn;

    public NumericValueAsString $standardDeviation;

    public NumericValueAsString $downsideDeviation;

    public NumericValueAsString $sharpeRatio;

    public NumericValueAsString $sortinoRatio;

    public function __construct(
        public NumericValueAsString $riskFreeRate = new NumericValueAsString(scale: 10),
    ) {
        $this->sumOfReturns = new NumericValueAsString(scale: self::WORK_SCALE);
        $this->sumOfSquaredReturns = new NumericValueAsString(scale: self::SQUARE_SCALE);
        $this->sumOfSquaredShortfalls = new NumericValueAsString(scale: self::WORK_SCALE);
        $this->meanReturn = new NumericValueAsString(scale: 10);
        $this->standardDeviation = new NumericValueAsString(scale: 10);
        $this->downsideDeviation = new NumericValueAsString(scale: 10);
        $this->sharpeRatio = new NumericValueAsString(scale: 4);
        $this->sortinoRatio = new NumericValueAsString(scale: 4);
    }

    /**
     * Fold one realized return into the running sums. Nothing about the individual return is
     * kept, so the series can be arbitrarily long.
     */
    public function recordReturn(NumericValueAsString $return): void
    {
        $return = $return->cloneWithScale(self::WORK_SCALE);

        $this->sampleSize++;

        $this->sumOfReturns->add($return);

        $this->sumOfSquaredReturns->add(
            $return->cloneWithScale(self::SQUARE_SCALE)->multiply(value: $return, immutable: true),
        );

        $shortfall = $return->subtract(value: $this->riskFreeRate, immutable: true);

        if ($shortfall->isLessThan(0)) {
            $this->sumOfSquaredShortfalls->add($shortfall->multiply(value: $shortfall, immutable: true));
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'sharpe_ratio' => (string) $this->sharpeRatio,
            'sortino_ratio' => (string) $this->sortinoRatio,
            'mean_return' => (string) $this->meanReturn,
            'standard_deviation' => (string) $this->standardDeviation,
            'downside_deviation' => (string) $this->downsideDeviation,
            'risk_free_rate' => (string) $this->riskFreeRate,
            'sample_size' => $this->sampleSize,
        ];
    }
}
