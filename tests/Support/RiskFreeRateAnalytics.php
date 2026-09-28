<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Support;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\RiskAdjustedReturns;

/**
 * An engine subclass with a non-zero risk-free rate — the documented way to set one — so the
 * golden vectors also pin the Sortino shortfall and the Sharpe excess against a real rate.
 */
final class RiskFreeRateAnalytics extends Analytics
{
    protected function initializeAnalyticsResults(): void
    {
        parent::initializeAnalyticsResults();

        if ($this->riskAdjustedReturns !== null) {
            $this->riskAdjustedReturns = new RiskAdjustedReturns(NumericValueAsString::of('0.0005'));
        }
    }
}
