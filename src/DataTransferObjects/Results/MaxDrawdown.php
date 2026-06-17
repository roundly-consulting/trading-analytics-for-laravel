<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class MaxDrawdown implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    /** Running cumulative realized equity (sum of net P&L). */
    public NumericValueAsString $equity;

    /** Highest equity reached so far (the running peak). */
    public NumericValueAsString $peak;

    /** Largest absolute peak-to-trough drop, as a positive amount. */
    public NumericValueAsString $value;

    /** Largest peak-to-trough drop as a percentage of the peak. */
    public NumericValueAsString $percentage;

    public function __construct(int $scale = 10)
    {
        $this->equity = new NumericValueAsString(scale: $scale);
        $this->peak = new NumericValueAsString(scale: $scale);
        $this->value = new NumericValueAsString(scale: $scale);
        $this->percentage = new NumericValueAsString(scale: 4);
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'value' => (string) $this->value,
            'percentage' => (string) $this->percentage,
            'equity' => (string) $this->equity,
            'peak' => (string) $this->peak,
        ];
    }
}
