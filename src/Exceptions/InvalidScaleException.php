<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

final class InvalidScaleException extends TradingAnalyticsException
{
    public static function negative(int $scale): self
    {
        return new self("The scale [{$scale}] must be zero or greater.");
    }
}
