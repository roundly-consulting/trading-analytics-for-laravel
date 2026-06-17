<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Feature\Fixtures;

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

/**
 * Deterministic generator for a realistic-but-reproducible set of trade rows.
 *
 * Every field is derived purely from the zero-based row index ($i) — there is no
 * randomness and no reliance on the current time, so the same index always yields
 * the same row. This lets the feature test seed a database table and then
 * independently re-compute the expected aggregates from the very same formula.
 *
 * The rows are shaped like a consumer's own `trades` table (snake_case columns),
 * not like the package's {@see Trade}.
 */
final class TradeDatasetGenerator
{
    /**
     * The fixed catalogue of currency pairs the generator cycles through, so the
     * dataset spans multiple base currencies, quote currencies and pairs.
     *
     * @var list<array{base: string, quote: string}>
     */
    private const PAIRS = [
        ['base' => 'BTC', 'quote' => 'USD'],
        ['base' => 'ETH', 'quote' => 'USD'],
        ['base' => 'XRP', 'quote' => 'USD'],
        ['base' => 'ETH', 'quote' => 'EUR'],
        ['base' => 'ADA', 'quote' => 'EUR'],
        ['base' => 'SOL', 'quote' => 'GBP'],
    ];

    /** Anchor for the deterministic open timestamps (UTC). */
    private const EPOCH = '2024-01-01 00:00:00';

    /**
     * Build every row for a dataset of the given size.
     *
     * @return list<array{
     *     base_currency: string,
     *     quote_currency: string,
     *     direction: string,
     *     open_price: int,
     *     close_price: int,
     *     size: int,
     *     commission: int|null,
     *     opened_at: string,
     *     closed_at: string|null
     * }>
     */
    public static function rows(int $count): array
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = self::row($i);
        }

        return $rows;
    }

    /**
     * Build a single deterministic row from its index.
     *
     * @return array{
     *     base_currency: string,
     *     quote_currency: string,
     *     direction: string,
     *     open_price: int,
     *     close_price: int,
     *     size: int,
     *     commission: int|null,
     *     opened_at: string,
     *     closed_at: string|null
     * }
     */
    public static function row(int $i): array
    {
        $pair = self::PAIRS[$i % count(self::PAIRS)];
        $direction = ($i % 2 === 0) ? 'buy' : 'sell';

        // Integer prices and sizes keep every derived figure (P&L, commission,
        // ROI numerator/denominator) exact, so the independent re-computation can
        // assert bcmath string equality without rounding drift.
        $openPrice = 100 + ($i % 40);          // 100..139
        $size = 1 + ($i % 5);                   // 1..5
        $delta = 1 + ($i % 9);                  // magnitude of the price move, 1..9

        // outcome: 0,1,2 => win, 3 => loss, 4 => breakeven (mod 5).
        $outcome = $i % 5;

        $favourable = match ($outcome) {
            3 => -1,       // loss: price moves against the position
            4 => 0,        // breakeven: no move
            default => 1,  // win: price moves in favour of the position
        };

        // A favourable move for a buy is a price increase; for a sell it is a
        // price decrease. Encode that so the realised P&L sign matches $outcome.
        $priceMove = $favourable * $delta * ($direction === 'buy' ? 1 : -1);
        $closePrice = $openPrice + $priceMove;

        // Nonzero commissions for most rows; every 6th row carries no commission
        // (null) so the dataset exercises the missing-commission path too.
        $commission = ($i % 6 === 0) ? null : (1 + ($i % 7));

        // Every 7th row is still open (no close timestamp) => unrealized.
        $isOpen = ($i % 7 === 0);

        $openedAt = date('Y-m-d H:i:s', strtotime(self::EPOCH) + ($i * 3600));
        $closedAt = $isOpen
            ? null
            : date('Y-m-d H:i:s', strtotime(self::EPOCH) + ($i * 3600) + (($i % 8) + 1) * 600);

        return [
            'base_currency' => $pair['base'],
            'quote_currency' => $pair['quote'],
            'direction' => $direction,
            'open_price' => $openPrice,
            'close_price' => $closePrice,
            'size' => $size,
            'commission' => $commission,
            'opened_at' => $openedAt,
            'closed_at' => $closedAt,
        ];
    }
}
