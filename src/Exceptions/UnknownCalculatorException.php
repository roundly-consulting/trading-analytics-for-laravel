<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

final class UnknownCalculatorException extends TradingAnalyticsException
{
    public static function notACalculator(string $class): self
    {
        return new self("The class [{$class}] is not a valid analytics calculator.");
    }
}
