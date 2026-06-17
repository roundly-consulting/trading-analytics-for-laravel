<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class CumulativeReturn implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    public NumericDirectionalAggregatesByCurrency $gross;

    public NumericDirectionalAggregatesByCurrency $net;

    public function __construct()
    {
        $this->gross = new NumericDirectionalAggregatesByCurrency;
        $this->net = new NumericDirectionalAggregatesByCurrency;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'gross' => $this->gross->toArray(),
            'net' => $this->net->toArray(),
        ];
    }
}
