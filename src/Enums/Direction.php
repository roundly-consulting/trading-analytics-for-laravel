<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Enums;

enum Direction: string
{
    case BUY = 'buy';
    case SELL = 'sell';

    public function isBuy(): bool
    {
        return $this === Direction::BUY;
    }

    public function isSell(): bool
    {
        return $this === Direction::SELL;
    }
}
