<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

class NumericByDirections
{
    public NumericValueAsString $total;

    public NumericValueAsString $buy;

    public NumericValueAsString $sell;

    public function __construct(int $scale = 10)
    {
        $this->total = new NumericValueAsString(scale: $scale);
        $this->buy = new NumericValueAsString(scale: $scale);
        $this->sell = new NumericValueAsString(scale: $scale);
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'total' => (string) $this->total,
            'buy' => (string) $this->buy,
            'sell' => (string) $this->sell,
        ];
    }
}
