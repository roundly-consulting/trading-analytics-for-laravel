<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Abstraction\BaseNumericDirectionalAggregatesByCurrencyCalculator;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

class TradesDuration extends BaseNumericDirectionalAggregatesByCurrencyCalculator
{
    protected static function shouldCalculatePerTrade(Analytics $analytics, Trade $trade): bool
    {
        return $trade->isRealized();
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->duration;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return new NumericValueAsString(
            value: $trade->openTime->diffInSeconds($trade->closeTime),
            scale: 2,
        );
    }
}
