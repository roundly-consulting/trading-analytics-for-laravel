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

    /** How many trades fed this aggregate — the divisor of {@see $average}. */
    public int $count = 0;

    public NumericValueAsString $average;

    /**
     * The power of ten {@see $average} is scaled by while a cumulative return keeps its running
     * growth product there as a mantissa: held as a plain decimal, a long losing streak's
     * product sank below the scale and truncated to 0.
     *
     * @internal
     */
    public int $averageExponent = 0;

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

    /** Whether {@see $highest} / {@see $lowest} hold an observed value yet. */
    private bool $hasExtremes = false;

    /**
     * Fold one trade's value in: it counts towards the average, adds to the total and may
     * become the highest or lowest.
     */
    public function record(NumericValueAsString $value, string $pair): void
    {
        $this->count++;
        $this->total->add($value);
        $this->trackExtremes($value, $pair);
    }

    /**
     * Keep the highest and lowest values seen. The first value sets both, so zero is a value
     * like any other rather than an "unset" marker (a break-even trade used to reset them).
     */
    public function trackExtremes(NumericValueAsString $value, string $pair): void
    {
        if (! $this->hasExtremes || $value->isGreaterThan($this->highest)) {
            $this->highest->set($value);
            $this->highestPair = $pair;
        }

        if (! $this->hasExtremes || $value->isLessThan($this->lowest)) {
            $this->lowest->set($value);
            $this->lowestPair = $pair;
        }

        $this->hasExtremes = true;
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
