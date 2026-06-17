<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Abstraction\BaseNumericDirectionalAggregatesByCurrencyCalculator;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

class TradingValue extends BaseNumericDirectionalAggregatesByCurrencyCalculator
{
    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->value;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->size->multiply(
            value: $trade->openPrice,
            immutable: true,
        );
    }
}
