<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Abstraction\BaseNumericDirectionalAggregatesByCurrencyCalculator;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

class RealizedGrossProfitAndLoss extends BaseNumericDirectionalAggregatesByCurrencyCalculator
{
    protected static function shouldCalculatePerTrade(Analytics $analytics, Trade $trade): bool
    {
        return $trade->isRealized();
    }

    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        parent::calculatePerTrade($analytics, $trade);
        static::calculateTotalProfitsAndLosses($analytics, $trade);
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->realizedProfitAndLoss->gross;
    }

    protected static function dtoForProfits(Analytics $analytics): NumericByCurrency
    {
        return $analytics->realizedProfitAndLoss->grossProfits;
    }

    protected static function dtoForLosses(Analytics $analytics): NumericByCurrency
    {
        return $analytics->realizedProfitAndLoss->grossLosses;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->profitAndLoss();
    }

    protected static function calculateTotalProfitsAndLosses(Analytics $analytics, Trade $trade): void
    {
        $pnl = static::value($trade);

        $dto = $pnl->isPositiveNonZero() ? static::dtoForProfits($analytics) : static::dtoForLosses($analytics);

        $dto->total->add($pnl);
        $dto->forPair($trade->pair())->add($pnl);
        $dto->forBaseCurrency($trade->baseCurrency)->add($pnl);
        $dto->forQuoteCurrency($trade->quoteCurrency)->add($pnl);
    }
}
