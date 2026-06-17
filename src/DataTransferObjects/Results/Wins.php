<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalByCurrency;

final class Wins extends NumericDirectionalByCurrency
{
    public NumericDirectionalByCurrency $winRatio;

    public function __construct()
    {
        parent::__construct(scale: 0);

        $this->winRatio = new NumericDirectionalByCurrency(scale: 2);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'win_ratio' => $this->winRatio->toArray(),
        ]);
    }
}
