<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

dataset('empty-trades', [
    'empty-trades' => [
        new LazyCollection([]),
    ],
]);

dataset('default-trades', [
    'default-trades' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('45000.00'),
                closePrice: new NumericValueAsString('45500.00'),
                size: new NumericValueAsString('0.1'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 1, 15, 12, 30),
                commission: new NumericValueAsString('15.00'),
                closeTime: Carbon::create(2023, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('3000.00'),
                closePrice: new NumericValueAsString('3200.00'),
                size: new NumericValueAsString('1.5'),
                direction: Direction::SELL,
                openTime: Carbon::create(2023, 2, 10, 9, 15),
                commission: new NumericValueAsString('10.00'),
                closeTime: Carbon::create(2023, 2, 10, 10),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('3000.00'),
                closePrice: new NumericValueAsString('3200.00'),
                size: new NumericValueAsString('10'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 3, 5, 16, 45),
                commission: new NumericValueAsString('982.00'),
                closeTime: Carbon::create(2023, 3, 5, 17, 30),
            ),
            new Trade(
                baseCurrency: 'XRP',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('1.00'),
                closePrice: new NumericValueAsString('1.15'),
                size: new NumericValueAsString('1000'),
                direction: Direction::SELL,
                openTime: Carbon::create(2023, 4, 20, 11),
                commission: new NumericValueAsString('2.00'),
                closeTime: Carbon::create(2023, 4, 20, 11, 45),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('3500.00'),
                closePrice: new NumericValueAsString('3430.00'),
                size: new NumericValueAsString('12'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 5, 15, 14, 30),
                commission: new NumericValueAsString('1123.00'),
                closeTime: Carbon::create(2023, 5, 15, 15),
            ),
        ]),
    ],
]);

dataset('btc-buys', [
    'btc-buys' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('45000.00'),
                closePrice: new NumericValueAsString('45215.00'),
                size: new NumericValueAsString('0.1'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 1, 15, 12, 30),
                commission: new NumericValueAsString('15.00'),
                closeTime: Carbon::create(2023, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('30000.00'),
                closePrice: new NumericValueAsString('29300.00'),
                size: new NumericValueAsString('1.2'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 2, 10, 9, 15),
                commission: new NumericValueAsString('130.00'),
                closeTime: Carbon::create(2023, 2, 10, 10),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('63000.00'),
                closePrice: new NumericValueAsString('61520.00'),
                size: new NumericValueAsString('0.000124321'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 3, 5, 16, 45),
                commission: new NumericValueAsString('8.00'),
                closeTime: Carbon::create(2023, 3, 5, 17, 30),
            ),
        ]),
    ],
]);

dataset('different-highest-and-lowest', [
    'different-highest-and-lowest' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000021'),
                size: new NumericValueAsString('15000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 1, 15, 12, 30),
                commission: new NumericValueAsString('15.00'),
                closeTime: Carbon::create(2023, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'EUR',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000008'),
                size: new NumericValueAsString('10000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 2, 10, 9, 15),
                commission: new NumericValueAsString('8.50'),
                closeTime: Carbon::create(2023, 2, 10, 10),
            ),
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'EUR',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.0000123'),
                size: new NumericValueAsString('10000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 3, 5, 16, 45),
                commission: new NumericValueAsString('8.00'),
                closeTime: Carbon::create(2023, 3, 5, 17, 30),
            ),
        ]),
    ],
]);

dataset('open-and-closed-trades', [
    'open-and-closed-trades' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000021'),
                size: new NumericValueAsString('15000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 1, 15, 12, 30),
                commission: null,
                closeTime: null,
            ),
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'EUR',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000008'),
                size: new NumericValueAsString('10000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 2, 10, 9, 15),
                commission: new NumericValueAsString('8.50'),
                closeTime: Carbon::create(2023, 2, 10, 10),
            ),
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'EUR',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.0000123'),
                size: new NumericValueAsString('10000'),
                direction: Direction::SELL,
                openTime: Carbon::create(2023, 3, 5, 16, 45),
                commission: new NumericValueAsString('8.00'),
                closeTime: Carbon::create(2023, 3, 5, 17, 30),
            ),
        ]),
    ],
]);

dataset('closed-with-returns-40-20-15', [
    'closed-with-returns-40-20-15' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 1, 15, 12, 30),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2023, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'EUR',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000012'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2023, 2, 10, 9, 15),
                commission: new NumericValueAsString('185.002'),
                closeTime: Carbon::create(2023, 2, 10, 10),
            ),
            new Trade(
                baseCurrency: 'SHIB',
                quoteCurrency: 'EUR',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.0000085'),
                size: new NumericValueAsString('3000000000'),
                direction: Direction::SELL,
                openTime: Carbon::create(2023, 3, 5, 16, 45),
                commission: new NumericValueAsString('314.001'),
                closeTime: Carbon::create(2023, 3, 5, 17, 30),
            ),
        ]),
    ],
]);

// In close-time order, as the sequential calculators (drawdown, streaks, cumulative return) require.
dataset('frequency-test-trades', [
    'frequency-test-trades' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 8, 1, 12),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 1, 12),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 8, 1, 15),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 1, 15),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 8, 2, 13),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 2, 13),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 8, 9, 14),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 9, 14, 10),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 8, 15, 16),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 15, 16, 30),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 8, 20, 14),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 20, 14, 5),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('0.00001'),
                closePrice: new NumericValueAsString('0.000014'),
                size: new NumericValueAsString('1500000000'),
                direction: Direction::SELL,
                openTime: Carbon::create(2024, 8, 21, 16, 30),
                commission: new NumericValueAsString('250.005'),
                closeTime: Carbon::create(2024, 8, 21, 16, 35),
            ),
        ]),
    ],
]);

dataset('streaks-test-trades', [
    'streaks-test-trades' => [
        new LazyCollection([
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('45000.00'),
                closePrice: new NumericValueAsString('45500.00'),
                size: new NumericValueAsString('0.1'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 1, 15, 12, 30),
                commission: new NumericValueAsString('15.00'),
                closeTime: Carbon::create(2024, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('45000.00'),
                closePrice: new NumericValueAsString('45500.00'),
                size: new NumericValueAsString('0.1'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 1, 15, 12, 30),
                commission: new NumericValueAsString('15.00'),
                closeTime: Carbon::create(2024, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('45000.00'),
                closePrice: new NumericValueAsString('45500.00'),
                size: new NumericValueAsString('0.1'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 1, 15, 12, 30),
                commission: new NumericValueAsString('15.00'),
                closeTime: Carbon::create(2024, 1, 15, 14, 30),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('3000.00'),
                closePrice: new NumericValueAsString('3200.00'),
                size: new NumericValueAsString('1.5'),
                direction: Direction::SELL,
                openTime: Carbon::create(2024, 2, 10, 9, 15),
                commission: new NumericValueAsString('10.00'),
                closeTime: Carbon::create(2024, 2, 10, 10),
            ),
            new Trade(
                baseCurrency: 'ETH',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('3000.00'),
                closePrice: new NumericValueAsString('3200.00'),
                size: new NumericValueAsString('10'),
                direction: Direction::BUY,
                openTime: Carbon::create(2024, 3, 5, 16, 45),
                commission: new NumericValueAsString('982.00'),
                closeTime: Carbon::create(2024, 3, 5, 17, 30),
            ),
            new Trade(
                baseCurrency: 'BTC',
                quoteCurrency: 'USD',
                openPrice: new NumericValueAsString('45000.00'),
                closePrice: new NumericValueAsString('45500.00'),
                size: new NumericValueAsString('0.1'),
                direction: Direction::SELL,
                openTime: Carbon::create(2024, 4, 20, 11),
                commission: new NumericValueAsString('2.00'),
                closeTime: Carbon::create(2024, 4, 20, 11, 45),
            ),
        ]),
    ],
]);
