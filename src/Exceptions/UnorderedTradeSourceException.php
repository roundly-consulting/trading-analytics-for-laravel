<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

use DateTimeInterface;

/**
 * The trades do not arrive in the order they closed. The maximum drawdown, the streaks and the
 * running cumulative return follow that order, so the package never guesses one: a query needs
 * an ORDER BY, and a realized trade that closed before the one read ahead of it is refused.
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

    public static function outOfOrder(DateTimeInterface $previous, DateTimeInterface $closedAt): self
    {
        return new self(
            'Trade analytics follow the order trades close in, but a trade closed at ['.$closedAt->format('Y-m-d H:i:s')
            .'] arrived after one closed at ['.$previous->format('Y-m-d H:i:s').']. Order the source by close time, '
            ."e.g. ->orderBy('close_time')->orderBy('id'), or sort the collection with ->sortBy('close_time').",
        );
    }
}
