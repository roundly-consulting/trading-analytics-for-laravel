<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

final class WinRateByPeriod
{
    /** @var array<string, int> */
    public array $wins = [];

    /** @var array<string, int> */
    public array $totals = [];

    /** @var array<string, NumericValueAsString> */
    public array $rates = [];

    public function __construct(public Period $period = Period::DAILY) {}

    public function record(string $bucket, bool $isWin): void
    {
        $this->totals[$bucket] = ($this->totals[$bucket] ?? 0) + 1;

        if ($isWin) {
            $this->wins[$bucket] = ($this->wins[$bucket] ?? 0) + 1;
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $rates = [];

        foreach ($this->rates as $bucket => $rate) {
            $rates[$bucket] = (string) $rate;
        }

        return [
            'period' => $this->period->value,
            'wins' => $this->wins,
            'totals' => $this->totals,
            'rates' => $rates,
        ];
    }
}
