<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;

class CumulativeReturn
{
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
