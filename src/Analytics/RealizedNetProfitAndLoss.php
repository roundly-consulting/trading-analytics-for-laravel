<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

class RealizedNetProfitAndLoss extends UnrealizedGrossProfitAndLoss
{
    protected static function shouldCalculatePerTrade(Analytics $analytics, Trade $trade): bool
    {
        return $trade->isRealized();
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->realizedProfitAndLoss->net;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->profitAndLoss(subtractComissions: true);
    }

    protected static function dtoForProfits(Analytics $analytics): NumericByCurrency
    {
        return $analytics->realizedProfitAndLoss->netProfits;
    }

    protected static function dtoForLosses(Analytics $analytics): NumericByCurrency
    {
        return $analytics->realizedProfitAndLoss->netLosses;
    }
}
