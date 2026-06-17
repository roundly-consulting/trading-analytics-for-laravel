<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class Expectancy implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    public int $winningTrades = 0;

    public int $losingTrades = 0;

    public NumericValueAsString $value;

    public NumericValueAsString $averageWin;

    public NumericValueAsString $averageLoss;

    public NumericValueAsString $winRate;

    public NumericValueAsString $lossRate;

    public function __construct(int $scale = 10)
    {
        $this->value = new NumericValueAsString(scale: $scale);
        $this->averageWin = new NumericValueAsString(scale: $scale);
        $this->averageLoss = new NumericValueAsString(scale: $scale);
        $this->winRate = new NumericValueAsString(scale: 4);
        $this->lossRate = new NumericValueAsString(scale: 4);
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'value' => (string) $this->value,
            'average_win' => (string) $this->averageWin,
            'average_loss' => (string) $this->averageLoss,
            'win_rate' => (string) $this->winRate,
            'loss_rate' => (string) $this->lossRate,
        ];
    }
}
