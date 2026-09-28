<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

use RoundlyConsulting\TradingAnalytics\Analytics;

final class InvalidEngineException extends TradingAnalyticsException
{
    public static function notAnAnalyticsEngine(string $class): self
    {
        return new self("The class [{$class}] is not an analytics engine: it must be [".Analytics::class.'] or extend it.');
    }
}
