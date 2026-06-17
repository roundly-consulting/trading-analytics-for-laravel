<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Abstraction\BaseNumericDirectionalAggregatesByCurrencyCalculator;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

class Commissions extends BaseNumericDirectionalAggregatesByCurrencyCalculator
{
    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->commission;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->commission;
    }

    protected static function shouldCalculatePerTrade(Analytics $analytics, Trade $trade): bool
    {
        return ! is_null($trade->commission);
    }
}
