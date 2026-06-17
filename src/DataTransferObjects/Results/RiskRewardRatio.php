<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;

final class RiskRewardRatio
{
    public int $winningTrades = 0;

    public int $losingTrades = 0;

    public NumericValueAsString $value;

    public NumericValueAsString $averageWin;

    public NumericValueAsString $averageLoss;

    public function __construct(int $scale = 4)
    {
        $this->value = new NumericValueAsString(scale: $scale);
        $this->averageWin = new NumericValueAsString(scale: 10);
        $this->averageLoss = new NumericValueAsString(scale: 10);
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'value' => (string) $this->value,
            'average_win' => (string) $this->averageWin,
            'average_loss' => (string) $this->averageLoss,
        ];
    }
}
