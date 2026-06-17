<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class NumericAggregates implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    public NumericValueAsString $total;

    public NumericValueAsString $average;

    public NumericValueAsString $highest;

    public string $highestPair = '';

    public NumericValueAsString $lowest;

    public string $lowestPair = '';

    public function __construct(protected int $scale = 10)
    {
        $this->total = new NumericValueAsString(scale: $scale);
        $this->average = new NumericValueAsString(scale: $scale);
        $this->highest = new NumericValueAsString(scale: $scale);
        $this->lowest = new NumericValueAsString(scale: $scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'total' => (string) $this->total,
            'average' => (string) $this->average,
            'highest' => [
                'value' => (string) $this->highest,
                'pair' => $this->highestPair,
            ],
            'lowest' => [
                'value' => (string) $this->lowest,
                'pair' => $this->lowestPair,
            ],
        ];
    }
}
