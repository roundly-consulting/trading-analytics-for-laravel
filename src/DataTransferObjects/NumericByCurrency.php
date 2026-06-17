<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\Traits\HasScale;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
class NumericByCurrency implements Arrayable, Jsonable, JsonSerializable
{
    use HasScale;
    use SerializesToJson;

    public NumericValueAsString $total;

    /** @var array<string, NumericValueAsString> */
    public array $perBaseCurrency = [];

    /** @var array<string, NumericValueAsString> */
    public array $perQuoteCurrency = [];

    /** @var array<string, NumericValueAsString> */
    public array $perPair = [];

    public function __construct(int $scale = 10)
    {
        $this->scale($scale);

        $this->total = new NumericValueAsString(scale: $this->scale);
    }

    public function forPair(string $pair): NumericValueAsString
    {
        return $this->perPair[$pair] ??= new NumericValueAsString(scale: $this->scale);
    }

    public function forBaseCurrency(string $baseCurrency): NumericValueAsString
    {
        return $this->perBaseCurrency[$baseCurrency] ??= new NumericValueAsString(scale: $this->scale);
    }

    public function forQuoteCurrency(string $quoteCurrency): NumericValueAsString
    {
        return $this->perQuoteCurrency[$quoteCurrency] ??= new NumericValueAsString(scale: $this->scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'total' => (string) $this->total,
            'per_pair' => $this->formatToString($this->perPair),
            'per_base_currency' => $this->formatToString($this->perBaseCurrency),
            'per_quote_currency' => $this->formatToString($this->perQuoteCurrency),
        ];
    }

    /**
     * @param  array<string, NumericValueAsString>  $items
     * @return array<string, string>
     */
    protected function formatToString(array $items): array
    {
        $result = [];

        /** @var NumericValueAsString $value */
        foreach ($items as $key => $value) {
            $result[$key] = (string) $value;
        }

        return $result;
    }

    /**
     * @param  array<string, NumericValueAsString>  $items
     * @return array<string, array<string, mixed>>
     */
    protected function formatToArray(array $items): array
    {
        $result = [];

        /** @var NumericValueAsString $value */
        foreach ($items as $key => $value) {
            $result[$key] = $value->toArray();
        }

        return $result;
    }
}
