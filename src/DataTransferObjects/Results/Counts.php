<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalByCurrency;

class Counts extends NumericDirectionalByCurrency
{
    public function __construct()
    {
        parent::__construct(0);
    }
}
