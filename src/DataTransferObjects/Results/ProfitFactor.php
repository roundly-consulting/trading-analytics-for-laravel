<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;

final class ProfitFactor extends NumericByCurrency
{
    public function __construct()
    {
        parent::__construct(scale: 2);
    }
}
