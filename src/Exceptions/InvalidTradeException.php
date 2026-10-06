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

    public static function nonPositiveSize(string $size): self
    {
        return new self("A trade's size must be greater than 0; got '{$size}'.");
    }

    public static function nonPositiveOpenPrice(string $price): self
    {
        return new self("A trade's open price must be greater than 0; got '{$price}'.");
    }

    public static function negativeClosePrice(string $price): self
    {
        return new self("A trade's close price cannot be negative; got '{$price}'.");
    }

    public static function missingField(string $field): self
    {
        return new self("A trade is missing the required '{$field}' field.");
    }

    public static function invalidField(string $field, string $expected, mixed $value): self
    {
        return new self("A trade's '{$field}' field must be {$expected}; got ".get_debug_type($value).'.');
    }

    public static function invalidDirection(string $value): self
    {
        return new self("'{$value}' is not a valid trade direction; expected one of: buy, sell.");
    }
}
