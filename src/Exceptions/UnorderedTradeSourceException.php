<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

/**
 * A query was passed as a trade source without an ORDER BY. The equity curve, drawdown,
 * streaks and running cumulative returns all depend on trade order, so the package never
 * guesses one and never runs unordered.
 */
final class UnorderedTradeSourceException extends TradingAnalyticsException
{
    public static function for(object $source): self
    {
        return new self(
            'Trade analytics depend on trade order, but the ['.$source::class.'] passed as a trade source has '
            ."no ORDER BY. Order the query chronologically, e.g. ->orderBy('close_time')->orderBy('id').",
        );
    }
}
