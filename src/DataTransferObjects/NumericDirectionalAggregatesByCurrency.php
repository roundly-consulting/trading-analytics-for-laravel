<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
class NumericDirectionalAggregatesByCurrency implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

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
            'per_pair' => $this->recordedAggregatesToArray($this->perPair),
            'per_base_currency' => $this->recordedAggregatesToArray($this->perBaseCurrency),
            'per_quote_currency' => $this->recordedAggregatesToArray($this->perQuoteCurrency),
        ];
    }

    /**
     * @param  array<string, NumericDirectionalAggregates>  $items
     * @return array<string, array<string, mixed>>
     */
    protected function recordedAggregatesToArray(array $items): array
    {
        $result = [];

        /** @var NumericDirectionalAggregates $analytics */
        foreach ($items as $key => $analytics) {
            // A key no trade fed (touched through forPair() and friends) is empty, not zero.
            if ($analytics->total->count === 0) {
                continue;
            }

            $result[$key] = $analytics->toArray();
        }

        return $result;
    }
}
