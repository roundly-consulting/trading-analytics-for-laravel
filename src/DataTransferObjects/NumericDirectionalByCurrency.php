<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

class NumericDirectionalByCurrency
{
    public NumericByDirections $global;

    /** @var array<string, NumericByDirections> */
    public array $perBaseCurrency = [];

    /** @var array<string, NumericByDirections> */
    public array $perQuoteCurrency = [];

    /** @var array<string, NumericByDirections> */
    public array $perPair = [];

    public function __construct(protected int $scale = 10)
    {
        $this->global = new NumericByDirections($this->scale);
    }

    public function forPair(string $pair): NumericByDirections
    {
        return $this->perPair[$pair] ??= new NumericByDirections($this->scale);
    }

    public function forBaseCurrency(string $baseCurrency): NumericByDirections
    {
        return $this->perBaseCurrency[$baseCurrency] ??= new NumericByDirections($this->scale);
    }

    public function forQuoteCurrency(string $quoteCurrency): NumericByDirections
    {
        return $this->perQuoteCurrency[$quoteCurrency] ??= new NumericByDirections($this->scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'global' => $this->global->toArray(),
            'per_base_currency' => $this->nonZeroNumericDirectionalAnalyticsToArray($this->perBaseCurrency),
            'per_quote_currency' => $this->nonZeroNumericDirectionalAnalyticsToArray($this->perQuoteCurrency),
            'per_pair' => $this->nonZeroNumericDirectionalAnalyticsToArray($this->perPair),
        ];
    }

    /**
     * @param  array<string, NumericByDirections>  $items
     * @return array<string, array<string, mixed>>
     */
    protected function nonZeroNumericDirectionalAnalyticsToArray(array $items): array
    {
        $result = [];

        /** @var NumericByDirections $numericDirectionalAnalytics */
        foreach ($items as $key => $numericDirectionalAnalytics) {
            if ($numericDirectionalAnalytics->total->isZero()) {
                continue;
            }

            $result[$key] = $numericDirectionalAnalytics->toArray();
        }

        return $result;
    }
}
