<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Support;

use Generator;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

/**
 * Deterministic trade datasets for the golden vectors and the memory regression tests.
 *
 * The random stream draws every field from a SHA-256 of `seed:index:field` rather than a
 * seeded PHP engine, so the data is byte-identical on every PHP build and platform — the
 * frozen vectors must never move because a PRNG algorithm did.
 */
final class TradeDatasets
{
    public const int SEED = 20260928;

    /** @var list<array{string, string}> */
    private const array PAIRS = [
        ['BTC', 'USD'],
        ['ETH', 'USD'],
        ['XRP', 'EUR'],
        ['SOL', 'USDT'],
        ['ETH', 'BTC'],
    ];

    /** The golden-vector datasets, in the order the frozen fixture lists them. */
    public const array GOLDEN = [
        'tiny',
        'single-trade',
        'all-winners',
        'mixed-signs',
        'zero-variance-gain',
        'zero-variance-loss',
        'no-realized-trades',
        'random-2500',
    ];

    /**
     * One golden-vector dataset, freshly built so it can be iterated again.
     *
     * @return LazyCollection<int, Trade>
     */
    public static function golden(string $name): LazyCollection
    {
        return match ($name) {
            'tiny' => Trade::collect(self::tiny()),
            'single-trade' => Trade::collect(self::singleTrade()),
            'all-winners' => Trade::collect(self::allWinners()),
            'mixed-signs' => Trade::collect(self::mixedSigns()),
            'zero-variance-gain' => Trade::collect(self::zeroVarianceGain()),
            'zero-variance-loss' => Trade::collect(self::zeroVarianceLoss()),
            'no-realized-trades' => Trade::collect(self::noRealizedTrades()),
            'random-2500' => Trade::collect(LazyCollection::make(static fn (): Generator => self::randomRows(2500))),
        };
    }

    /**
     * A lazy stream of `$count` pseudo-random trade rows in the `Trade::fromArray()` shape, in
     * close-time order — as a query ordered by `close_time` returns them, and as the drawdown,
     * streaks and cumulative return require. Each trade was opened up to a day before it
     * closed, so the open times interleave. About 10% are open positions; 25% carry no
     * commission.
     *
     * `$spacing` is the gap in seconds between two closes (plus up to one gap of jitter, which
     * keeps the closes in order). The golden vectors use the default; the memory tests shrink
     * it so a long stream spans few win-rate buckets — those buckets are output, sized by the
     * calendar, not by the history.
     *
     * @return Generator<int, array<string, string|null>>
     */
    public static function randomRows(int $count, int $seed = self::SEED, int $spacing = 1800): Generator
    {
        $epoch = 1704067200; // 2024-01-01 00:00:00 UTC

        for ($i = 0; $i < $count; $i++) {
            [$base, $quote] = self::PAIRS[self::draw($seed, $i, 'pair', count(self::PAIRS))];

            $openCents = 100 + self::draw($seed, $i, 'open', 5_000_000);
            $moveBasisPoints = self::draw($seed, $i, 'move', 2001) - 1000; // ±10%
            $closeCents = max(1, $openCents + intdiv($openCents * $moveBasisPoints, 10_000));

            // Row i closes within [i, i + 1) spacings of the epoch, so the closes never run backwards.
            $closedAt = $epoch + $i * $spacing + self::draw($seed, $i, 'jitter', $spacing);
            $openedAt = $closedAt - 60 - self::draw($seed, $i, 'hold', 86_400);
            $isOpen = self::draw($seed, $i, 'is-open', 10) === 0;

            yield [
                'base_currency' => $base,
                'quote_currency' => $quote,
                'open_price' => self::decimal($openCents, 2),
                'close_price' => self::decimal($closeCents, 2),
                'size' => self::decimal(1 + self::draw($seed, $i, 'size', 100_000), 3),
                'direction' => self::draw($seed, $i, 'direction', 2) === 0 ? 'buy' : 'sell',
                'open_time' => gmdate('Y-m-d H:i:s', $openedAt),
                'commission' => self::draw($seed, $i, 'has-fee', 4) === 0
                    ? null
                    : self::decimal(self::draw($seed, $i, 'fee', 5000), 2),
                'close_time' => $isOpen ? null : gmdate('Y-m-d H:i:s', $closedAt),
            ];
        }
    }

    /**
     * Two realized trades on two pairs plus one open position.
     *
     * @return list<array<string, string|null>>
     */
    private static function tiny(): array
    {
        return [
            self::row('BTC', 'USD', '100', '110', '1', 'buy', '2024-01-01 10:00:00', '2024-01-01 11:00:00'),
            self::row('ETH', 'USD', '50', '53', '2', 'sell', '2024-01-02 10:00:00', '2024-01-02 12:00:00', '0.5'),
            self::row('BTC', 'USD', '120', '125', '1', 'buy', '2024-01-03 10:00:00'),
        ];
    }

    /** @return list<array<string, string|null>> */
    private static function singleTrade(): array
    {
        return [
            self::row('BTC', 'USD', '45000.00', '44100.00', '0.25', 'buy', '2024-02-01 09:00:00', '2024-02-01 17:30:00', '12.50'),
        ];
    }

    /** @return list<array<string, string|null>> */
    private static function allWinners(): array
    {
        return [
            self::row('BTC', 'USD', '40000', '41000', '0.1', 'buy', '2024-03-01 10:00:00', '2024-03-01 12:00:00', '2'),
            self::row('ETH', 'USD', '3000', '2900', '1.5', 'sell', '2024-03-02 10:00:00', '2024-03-02 11:00:00', '1.25'),
            self::row('XRP', 'EUR', '0.60', '0.66', '1000', 'buy', '2024-03-03 10:00:00', '2024-03-04 10:00:00'),
            self::row('BTC', 'USD', '42000', '42420', '0.05', 'buy', '2024-03-05 10:00:00', '2024-03-05 10:30:00', '0.5'),
            self::row('ETH', 'USD', '3100', '3007', '2', 'sell', '2024-03-06 10:00:00', '2024-03-06 18:00:00'),
            self::row('XRP', 'EUR', '0.70', '0.75', '500', 'buy', '2024-03-08 10:00:00', '2024-03-09 09:00:00', '0.1'),
            self::row('BTC', 'USD', '43000', '43860', '0.2', 'buy', '2024-03-10 10:00:00', '2024-03-10 14:00:00', '3'),
            self::row('ETH', 'USD', '3200', '3040', '0.75', 'sell', '2024-03-12 10:00:00', '2024-03-13 10:00:00', '0.8'),
        ];
    }

    /** @return list<array<string, string|null>> */
    private static function mixedSigns(): array
    {
        return [
            self::row('BTC', 'USD', '30000', '31500', '0.1', 'buy', '2024-04-01 10:00:00', '2024-04-01 11:00:00', '5'),
            self::row('BTC', 'USD', '31500', '30000', '0.1', 'buy', '2024-04-02 10:00:00', '2024-04-02 11:00:00', '5'),
            self::row('ETH', 'USD', '2000', '1900', '1', 'sell', '2024-04-03 10:00:00', '2024-04-03 15:00:00', '2'),
            self::row('ETH', 'USD', '1900', '2050', '1', 'sell', '2024-04-04 10:00:00', '2024-04-04 15:00:00', '2'),
            self::row('XRP', 'EUR', '0.50', '0.50', '2000', 'buy', '2024-04-05 10:00:00', '2024-04-05 10:05:00'),
            self::row('XRP', 'EUR', '0.50', '0.5005', '2000', 'buy', '2024-04-06 10:00:00', '2024-04-06 10:05:00', '1.5'),
            self::row('SOL', 'USDT', '100', '130', '10', 'buy', '2024-04-07 10:00:00', '2024-04-09 10:00:00', '4'),
            self::row('SOL', 'USDT', '130', '91', '10', 'buy', '2024-04-10 10:00:00', '2024-04-12 10:00:00', '4'),
            self::row('ETH', 'BTC', '0.05', '0.048', '20', 'sell', '2024-04-13 10:00:00', '2024-04-13 20:00:00', '0.0001'),
            self::row('BTC', 'USD', '29000', '29290', '0.3', 'buy', '2024-04-14 10:00:00', '2024-04-14 12:00:00', '100'),
            self::row('ETH', 'USD', '2100', '2310', '0.5', 'sell', '2024-04-15 10:00:00', '2024-04-16 10:00:00'),
            self::row('SOL', 'USDT', '95', '99.75', '8', 'buy', '2024-04-17 10:00:00', '2024-04-17 12:00:00', '0.4'),
            self::row('BTC', 'USD', '29500', '28025', '0.2', 'buy', '2024-04-18 10:00:00', '2024-04-19 10:00:00', '6'),
            self::row('XRP', 'EUR', '0.55', '0.605', '1000', 'buy', '2024-04-20 10:00:00', '2024-04-20 11:00:00'),
            self::row('ETH', 'USD', '2200', '2090', '1', 'sell', '2024-04-21 10:00:00', '2024-04-21 11:00:00', '1'),
            self::row('BTC', 'USD', '30000', '30300', '0.1', 'buy', '2024-04-22 10:00:00'),
        ];
    }

    /**
     * Every realized trade returns exactly +10% net, so the standard deviation is zero.
     *
     * @return list<array<string, string|null>>
     */
    private static function zeroVarianceGain(): array
    {
        return [
            self::row('BTC', 'USD', '100', '110', '1', 'buy', '2024-05-01 10:00:00', '2024-05-01 11:00:00'),
            self::row('BTC', 'USD', '200', '220', '3', 'buy', '2024-05-02 10:00:00', '2024-05-02 11:00:00'),
            self::row('ETH', 'USD', '50', '45', '4', 'sell', '2024-05-03 10:00:00', '2024-05-03 11:00:00'),
            self::row('XRP', 'EUR', '0.40', '0.44', '250', 'buy', '2024-05-04 10:00:00', '2024-05-04 11:00:00'),
            self::row('ETH', 'USD', '80', '72', '0.5', 'sell', '2024-05-05 10:00:00', '2024-05-05 11:00:00'),
        ];
    }

    /**
     * Every realized trade returns exactly -5% net: zero deviation, non-zero downside.
     *
     * @return list<array<string, string|null>>
     */
    private static function zeroVarianceLoss(): array
    {
        return [
            self::row('BTC', 'USD', '100', '95', '1', 'buy', '2024-06-01 10:00:00', '2024-06-01 11:00:00'),
            self::row('ETH', 'USD', '40', '42', '5', 'sell', '2024-06-02 10:00:00', '2024-06-02 11:00:00'),
            self::row('SOL', 'USDT', '20', '19', '10', 'buy', '2024-06-03 10:00:00', '2024-06-03 11:00:00'),
            self::row('BTC', 'USD', '300', '315', '0.2', 'sell', '2024-06-04 10:00:00', '2024-06-04 11:00:00'),
        ];
    }

    /**
     * Open positions only: the realized return series is empty.
     *
     * @return list<array<string, string|null>>
     */
    private static function noRealizedTrades(): array
    {
        return [
            self::row('BTC', 'USD', '100', '104', '1', 'buy', '2024-07-01 10:00:00'),
            self::row('ETH', 'USD', '50', '49', '2', 'sell', '2024-07-02 10:00:00', null, '0.25'),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private static function row(
        string $base,
        string $quote,
        string $open,
        string $close,
        string $size,
        string $direction,
        string $openedAt,
        ?string $closedAt = null,
        ?string $commission = null,
    ): array {
        return [
            'base_currency' => $base,
            'quote_currency' => $quote,
            'open_price' => $open,
            'close_price' => $close,
            'size' => $size,
            'direction' => $direction,
            'open_time' => $openedAt,
            'commission' => $commission,
            'close_time' => $closedAt,
        ];
    }

    /** A uniform draw in [0, $range) derived from SHA-256 — stable on every platform. */
    private static function draw(int $seed, int $index, string $field, int $range): int
    {
        return (int) hexdec(substr(hash('sha256', "{$seed}:{$index}:{$field}"), 0, 12)) % $range;
    }

    /** Render an integer count of 10^-$places units as a fixed-point decimal string. */
    private static function decimal(int $units, int $places): string
    {
        $divisor = 10 ** $places;

        return intdiv($units, $divisor).'.'.str_pad((string) ($units % $divisor), $places, '0', STR_PAD_LEFT);
    }
}
