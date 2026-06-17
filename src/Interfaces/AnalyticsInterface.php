<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Interfaces;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

interface AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void;

    public static function calculateAfterTrades(Analytics $analytics): void;
}
