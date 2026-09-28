<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByDirections;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalByCurrency;

/**
 * Winning trades (realized P&L above zero) and the win ratio: wins over closed trades. Open
 * trades have no outcome yet, so they count towards neither.
 */
final class Wins extends NumericDirectionalByCurrency
{
    /** Closed trades per key — the denominator of {@see $winRatio}. */
    public NumericDirectionalByCurrency $closed;

    public NumericDirectionalByCurrency $winRatio;

    public function __construct()
    {
        parent::__construct(scale: 0);

        $this->closed = new NumericDirectionalByCurrency(scale: 0);
        $this->winRatio = new NumericDirectionalByCurrency(scale: 2);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            // Every key with a closed trade, a 0.00 ratio included: a zero is a real ratio.
            'win_ratio' => [
                'global' => $this->winRatio->global->toArray(),
                'per_base_currency' => $this->ratiosToArray($this->winRatio->perBaseCurrency, $this->closed->perBaseCurrency),
                'per_quote_currency' => $this->ratiosToArray($this->winRatio->perQuoteCurrency, $this->closed->perQuoteCurrency),
                'per_pair' => $this->ratiosToArray($this->winRatio->perPair, $this->closed->perPair),
            ],
        ]);
    }

    /**
     * The ratios of the keys that had a closed trade — not of a key merely read through
     * `forPair()` and friends, which materialise an empty entry.
     *
     * @param  array<string, NumericByDirections>  $ratios
     * @param  array<string, NumericByDirections>  $closed
     * @return array<string, array<string, string>>
     */
    private function ratiosToArray(array $ratios, array $closed): array
    {
        return array_map(
            static fn (NumericByDirections $ratio): array => $ratio->toArray(),
            array_intersect_key($ratios, $closed),
        );
    }
}
