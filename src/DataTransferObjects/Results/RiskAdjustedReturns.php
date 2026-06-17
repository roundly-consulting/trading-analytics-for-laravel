<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/**
 * Variance-based risk-adjusted return ratios. These require the full per-trade
 * return series (mean + deviation), so they are produced on the package's
 * separated multi-pass path rather than the single-pass aggregate path.
 *
 * @implements Arrayable<string, mixed>
 */
final class RiskAdjustedReturns implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    /** @var list<NumericValueAsString> */
    public array $returns = [];

    public NumericValueAsString $meanReturn;

    public NumericValueAsString $standardDeviation;

    public NumericValueAsString $downsideDeviation;

    public NumericValueAsString $sharpeRatio;

    public NumericValueAsString $sortinoRatio;

    public function __construct(
        public NumericValueAsString $riskFreeRate = new NumericValueAsString(scale: 10),
    ) {
        $this->meanReturn = new NumericValueAsString(scale: 10);
        $this->standardDeviation = new NumericValueAsString(scale: 10);
        $this->downsideDeviation = new NumericValueAsString(scale: 10);
        $this->sharpeRatio = new NumericValueAsString(scale: 4);
        $this->sortinoRatio = new NumericValueAsString(scale: 4);
    }

    public function recordReturn(NumericValueAsString $return): void
    {
        $this->returns[] = $return;
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
            'sample_size' => count($this->returns),
        ];
    }
}
