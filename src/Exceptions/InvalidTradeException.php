<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

final class InvalidTradeException extends TradingAnalyticsException
{
    public static function emptyCurrency(string $field): self
    {
        return new self("A trade's {$field} must be a non-empty currency symbol.");
    }

    public static function closeBeforeOpen(): self
    {
        return new self("A trade's close time cannot be before its open time.");
    }
}
