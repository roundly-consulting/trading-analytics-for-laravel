<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

class Counts implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        // Global, per pair, per base currency and per quote currency total number of trades
        $analytics->counts->global->total->add(1);
        $analytics->counts->forPair($trade->pair())->total->add(1);
        $analytics->counts->forBaseCurrency($trade->baseCurrency)->total->add(1);
        $analytics->counts->forQuoteCurrency($trade->quoteCurrency)->total->add(1);

        if ($trade->direction->isBuy()) {
            // Global, per pair, per base currency and per quote currency total number of trades by direction Buy
            $analytics->counts->global->buy->add(1);
            $analytics->counts->forPair($trade->pair())->buy->add(1);
            $analytics->counts->forBaseCurrency($trade->baseCurrency)->buy->add(1);
            $analytics->counts->forQuoteCurrency($trade->quoteCurrency)->buy->add(1);
        } else {
            // Global, per pair, per base currency and per quote currency total number of trades by direction Sell
            $analytics->counts->global->sell->add(1);
            $analytics->counts->forPair($trade->pair())->sell->add(1);
            $analytics->counts->forBaseCurrency($trade->baseCurrency)->sell->add(1);
            $analytics->counts->forQuoteCurrency($trade->quoteCurrency)->sell->add(1);
        }
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        //
    }
}
