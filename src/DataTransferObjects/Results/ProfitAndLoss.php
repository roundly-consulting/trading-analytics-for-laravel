<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class ProfitAndLoss implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    public NumericDirectionalAggregatesByCurrency $gross;

    public NumericByCurrency $grossProfits;

    public NumericByCurrency $grossLosses;

    public NumericDirectionalAggregatesByCurrency $net;

    public NumericByCurrency $netProfits;

    public NumericByCurrency $netLosses;

    public function __construct(int $scale = 10)
    {
        $this->gross = new NumericDirectionalAggregatesByCurrency($scale);
        $this->grossProfits = new NumericByCurrency($scale);
        $this->grossLosses = new NumericByCurrency($scale);

        $this->net = new NumericDirectionalAggregatesByCurrency($scale);
        $this->netProfits = new NumericByCurrency($scale);
        $this->netLosses = new NumericByCurrency($scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'gross' => [
                'pnl' => $this->gross->toArray(),
                'profits' => $this->grossProfits->toArray(),
                'losses' => $this->grossLosses->toArray(),
            ],
            'net' => [
                'pnl' => $this->net->toArray(),
                'profits' => $this->netProfits->toArray(),
                'losses' => $this->netLosses->toArray(),
            ],
        ];
    }
}
