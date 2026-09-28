<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

final class InvalidChunkSizeException extends TradingAnalyticsException
{
    public static function tooSmall(int $chunk): self
    {
        return new self("The chunk size a query source is paged with must be at least 1; got {$chunk}.");
    }
}
