<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;

class TradesDuration extends NumericDirectionalAggregatesByCurrency
{
    public function __construct(protected int $scale = 2)
    {
        parent::__construct($scale);
    }
}
