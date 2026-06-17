<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

final class InvalidNumericOperationException extends TradingAnalyticsException
{
    public static function nonNumericValue(string $value): self
    {
        return new self("The value [{$value}] is not numeric and cannot be used in a numeric operation.");
    }

    public static function fractionalExponent(string $exponent): self
    {
        return new self("The exponent [{$exponent}] must be an integer; fractional exponents are not supported by bcmath.");
    }
}
