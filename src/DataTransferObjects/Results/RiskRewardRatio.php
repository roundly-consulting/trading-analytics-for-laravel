<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class RiskRewardRatio implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    public int $winningTrades = 0;

    public int $losingTrades = 0;

    public NumericValueAsString $value;

    public NumericValueAsString $averageWin;

    public NumericValueAsString $averageLoss;

    /**
     * @param  int  $scale  decimal places of the ratio
     * @param  int  $amountScale  decimal places of the average win / loss — the run's scale
     */
    public function __construct(int $scale = 4, int $amountScale = 10)
    {
        $this->value = new NumericValueAsString(scale: $scale);
        $this->averageWin = new NumericValueAsString(scale: $amountScale);
        $this->averageLoss = new NumericValueAsString(scale: $amountScale);
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
