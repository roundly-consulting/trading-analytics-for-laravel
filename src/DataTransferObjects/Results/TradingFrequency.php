<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;

/**
 * How often trades are opened, globally and per pair / base / quote currency.
 *
 * The pass keeps, per key, only the number of opens and the earliest and latest open time:
 * the average gap between consecutive opens is (latest − earliest) / (opens − 1) in any
 * order, so the figure needs neither sorted input nor any per-trade history.
 */
final class TradingFrequency extends NumericByCurrency
{
    /** @var array<string, int> */
    protected array $opens = [];

    /** @var array<string, int> */
    protected array $earliest = [];

    /** @var array<string, int> */
    protected array $latest = [];

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

    /** Fold one open time (a Unix timestamp) into a key. */
    public function record(string $key, int $timestamp): void
    {
        $this->opens[$key] = ($this->opens[$key] ?? 0) + 1;
        $this->earliest[$key] = min($this->earliest[$key] ?? $timestamp, $timestamp);
        $this->latest[$key] = max($this->latest[$key] ?? $timestamp, $timestamp);
    }

    /** How many opens a key has seen. */
    public function opens(string $key): int
    {
        return $this->opens[$key] ?? 0;
    }

    /**
     * The pairs or currencies recorded under one breakdown (`pair`, `base` or `quote`), in
     * first-seen order.
     *
     * @return list<string>
     */
    public function keysOf(string $breakdown): array
    {
        $keys = [];

        foreach (array_keys($this->opens) as $key) {
            if (str_starts_with((string) $key, "{$breakdown}:")) {
                $keys[] = substr((string) $key, strlen($breakdown) + 1);
            }
        }

        return $keys;
    }

    /** Seconds between a key's earliest and latest open. */
    public function span(string $key): int
    {
        return ($this->latest[$key] ?? 0) - ($this->earliest[$key] ?? 0);
    }
}
