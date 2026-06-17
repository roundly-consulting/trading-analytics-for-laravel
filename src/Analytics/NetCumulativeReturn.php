<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

class NetCumulativeReturn extends GrossCumulativeReturn
{
    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->cumulativeReturn->net;
    }

    protected static function getReturnFromTrade(Trade $trade): NumericValueAsString
    {
        return $trade->roi(subtractComissions: true, asPercentage: false);
    }
}
