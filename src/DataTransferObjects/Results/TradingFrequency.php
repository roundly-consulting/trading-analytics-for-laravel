<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;

final class TradingFrequency extends NumericByCurrency
{
    /** @var array<string, int> */
    protected array $lastTradeTimestamp = [];

    /** @var array<string, int> */
    protected array $timeDifference = [];

    public function __construct(int $scale = 1)
    {
        parent::__construct($scale);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'total' => $this->total->toArray(),
            'per_pair' => $this->formatToArray($this->perPair),
            'per_base_currency' => $this->formatToArray($this->perBaseCurrency),
            'per_quote_currency' => $this->formatToArray($this->perQuoteCurrency),
        ];
    }

    public function incrementTimeDifference(string $key, int $by): void
    {
        $this->setTimeDifference(
            $key,
            $this->getTimeDifference($key) + $by
        );
    }

    public function setTimeDifference(string $key, int $difference): void
    {
        $this->timeDifference[$key] = $difference;
    }

    public function setLastTradeTimestamp(string $key, int $timestamp): void
    {
        $this->lastTradeTimestamp[$key] = $timestamp;
    }

    public function getTimeDifference(string $key): int
    {
        return $this->timeDifference[$key] ?? 0;
    }

    public function getLastTradeTimestamp(string $key): int
    {
        return $this->lastTradeTimestamp[$key] ?? 0;
    }
}
