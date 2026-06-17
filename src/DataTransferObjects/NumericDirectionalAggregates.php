<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

class NumericDirectionalAggregates
{
    public NumericAggregates $total;

    public NumericAggregates $buy;

    public NumericAggregates $sell;

    public function __construct(protected int $scale = 10)
    {
        $this->total = new NumericAggregates(scale: $scale);
        $this->buy = new NumericAggregates(scale: $scale);
        $this->sell = new NumericAggregates(scale: $scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'total' => $this->total->toArray(),
            'buy' => $this->buy->toArray(),
            'sell' => $this->sell->toArray(),
        ];
    }
}
