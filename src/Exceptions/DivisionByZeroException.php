<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

final class DivisionByZeroException extends TradingAnalyticsException
{
    public static function make(): self
    {
        return new self('Cannot divide a numeric value by zero.');
    }
}
