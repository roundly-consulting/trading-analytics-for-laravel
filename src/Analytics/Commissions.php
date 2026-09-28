<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Abstraction\BaseNumericDirectionalAggregatesByCurrencyCalculator;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

/**
 * Commission paid per trade. A trade without a commission paid none — the same reading the
 * net P&L takes — so it counts as zero: the average is per trade and the lowest can be 0.
 */
class Commissions extends BaseNumericDirectionalAggregatesByCurrencyCalculator
{
    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->commission;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->commission ?? new NumericValueAsString;
    }
}
