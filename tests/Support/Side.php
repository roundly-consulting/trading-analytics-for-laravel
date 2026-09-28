<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Support;

/**
 * A host application's own direction enum — not the package's `Direction` — to prove a
 * model casting its column to any string-backed enum still maps onto a trade.
 */
enum Side: string
{
    case Long = 'buy';
    case Short = 'sell';
}
