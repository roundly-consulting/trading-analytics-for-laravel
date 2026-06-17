<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalByCurrency;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class Streaks implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    public NumericDirectionalByCurrency $wins;

    public NumericDirectionalByCurrency $losses;

    /** @var array<string, int> */
    protected array $current = [];

    public function __construct()
    {
        $this->wins = new NumericDirectionalByCurrency(0);
        $this->losses = new NumericDirectionalByCurrency(0);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'wins' => $this->wins->toArray(),
            'losses' => $this->losses->toArray(),
        ];
    }

    public function current(string $key, bool $isWin): int
    {
        return $this->current[$this->currentStreakKey($key, $isWin)] ?? 0;
    }

    public function incrementCurrent(string $key, bool $isWin): void
    {
        $this->current[$this->currentStreakKey($key, $isWin)] = $this->current($key, $isWin) + 1;
    }

    public function resetCurrent(string $key, bool $isWin): void
    {
        $this->current[$this->currentStreakKey($key, $isWin)] = 0;
    }

    protected function currentStreakKey(string $key, bool $isWin): string
    {
        return implode('_', [$key, $isWin ? 'win' : 'loss']);
    }
}
