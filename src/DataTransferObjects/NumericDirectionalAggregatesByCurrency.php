<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

class NumericDirectionalAggregatesByCurrency
{
    public NumericDirectionalAggregates $global;

    /** @var array<string, NumericDirectionalAggregates> */
    public array $perBaseCurrency = [];

    /** @var array<string, NumericDirectionalAggregates> */
    public array $perQuoteCurrency = [];

    /** @var array<string, NumericDirectionalAggregates> */
    public array $perPair = [];

    public function __construct(protected int $scale = 10)
    {
        $this->global = new NumericDirectionalAggregates($this->scale);
    }

    public function forPair(string $pair): NumericDirectionalAggregates
    {
        return $this->perPair[$pair] ??= new NumericDirectionalAggregates($this->scale);
    }

    public function forBaseCurrency(string $baseCurrency): NumericDirectionalAggregates
    {
        return $this->perBaseCurrency[$baseCurrency] ??= new NumericDirectionalAggregates($this->scale);
    }

    public function forQuoteCurrency(string $quoteCurrency): NumericDirectionalAggregates
    {
        return $this->perQuoteCurrency[$quoteCurrency] ??= new NumericDirectionalAggregates($this->scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'global' => $this->global->toArray(),
            'per_pair' => $this->nonZeroNumericAnalyticsByDirectionsToArray($this->perPair),
            'per_base_currency' => $this->nonZeroNumericAnalyticsByDirectionsToArray($this->perBaseCurrency),
            'per_quote_currency' => $this->nonZeroNumericAnalyticsByDirectionsToArray($this->perQuoteCurrency),
        ];
    }

    /**
     * @param  array<string, NumericDirectionalAggregates>  $items
     * @return array<string, array<string, mixed>>
     */
    protected function nonZeroNumericAnalyticsByDirectionsToArray(array $items): array
    {
        $result = [];

        /** @var NumericDirectionalAggregates $analytics */
        foreach ($items as $key => $analytics) {
            if ($analytics->total->total->isZero()) {
                continue;
            }

            $result[$key] = $analytics->toArray();
        }

        return $result;
    }
}
