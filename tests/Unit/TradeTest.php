<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Enums\Period;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Tests\Support\Side;

it('returns correctly pair', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade->pair())->toBe('SHIB/USD');
})->with('closed-with-returns-40-20-15');

it('returns correctly whether trade is realized or open', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade)
        ->isOpen()->toBeTrue()
        ->isRealized()->toBeFalse();

    $trade = $trades->get(1);

    expect($trade)
        ->isOpen()->toBeFalse()
        ->isRealized()->toBeTrue();
})->with('open-and-closed-trades');

it('calculates correctly pnl', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade)
        ->profitAndLoss()->toString()->toBe('6000.0000000000')
        ->profitAndLoss(true)->toString()->toBe('5749.9950000000');

    $trade = $trades->get(1);

    expect($trade)
        ->profitAndLoss()->toString()->toBe('3000.0000000000')
        ->profitAndLoss(true)->toString()->toBe('2814.9980000000');

    $trade = $trades->get(2);

    expect($trade)
        ->profitAndLoss()->toString()->toBe('4500.0000000000')
        ->profitAndLoss(true)->toString()->toBe('4185.9990000000');
})->with('closed-with-returns-40-20-15');

it('calculates correctly roi', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade)
        ->roi(asPercentage: false)->toString()->toBe('0.4000000000')
        ->roi()->toString()->toBe('40.00')
        ->roi(true)->toString()->toBe('38.33')
        ->roi(subtractCommissions: true, asPercentage: false)->toString()->toBe('0.3833330000');

    $trade = $trades->get(1);

    expect($trade)
        ->roi(asPercentage: false)->toString()->toBe('0.2000000000')
        ->roi()->toString()->toBe('20.00')
        ->roi(true)->toString()->toBe('18.77')
        ->roi(subtractCommissions: true, asPercentage: false)->toString()->toBe('0.1876665333');

    $trade = $trades->get(2);

    expect($trade)
        ->roi(asPercentage: false)->toString()->toBe('0.1500000000')
        ->roi()->toString()->toBe('15.00')
        ->roi(true)->toString()->toBe('13.95')
        ->roi(subtractCommissions: true, asPercentage: false)->toString()->toBe('0.1395333000');
})->with('closed-with-returns-40-20-15');

it('returns trade as array', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade->toArray())->toBe([
        'base_currency' => 'SHIB',
        'quote_currency' => 'USD',
        'open_price' => '0.0000100000',
        'close_price' => '0.0000140000',
        'size' => '1500000000.0000000000',
        'direction' => 'buy',
        'open_time' => '2023-01-15 12:30:00',
        'close_time' => '2023-01-15 14:30:00',
        'commission' => '250.0050000000',
        'is_realized' => true,
        'is_open' => false,
        'pnl' => [
            'gross' => '6000.0000000000',
            'net' => '5749.9950000000',
        ],
        'roi' => [
            'gross' => '40.00',
            'net' => '38.33',
        ],
    ]);

})->with('closed-with-returns-40-20-15');

it('rejects an empty base or quote currency', function (string $base, string $quote) {
    expect(fn () => new Trade(
        baseCurrency: $base,
        quoteCurrency: $quote,
        openPrice: new NumericValueAsString('1'),
        closePrice: new NumericValueAsString('2'),
        size: new NumericValueAsString('1'),
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
    ))->toThrow(InvalidTradeException::class);
})->with([
    ['', 'USD'],
    ['  ', 'USD'],
    ['BTC', ''],
    ['BTC', '   '],
]);

it('rejects a close time before the open time', function () {
    expect(fn () => new Trade(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: new NumericValueAsString('1'),
        closePrice: new NumericValueAsString('2'),
        size: new NumericValueAsString('1'),
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
        closeTime: Carbon::create(2024, 1, 1, 11),
    ))->toThrow(InvalidTradeException::class);
});

it('constructs a valid open trade with all defaults', function () {
    $trade = new Trade(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: new NumericValueAsString('1'),
        closePrice: new NumericValueAsString('2'),
        size: new NumericValueAsString('1'),
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
    );

    expect($trade)
        ->isOpen()->toBeTrue()
        ->isRealized()->toBeFalse()
        ->and($trade->commission)->toBeNull();
});

it('constructs a valid realized trade', function () {
    $trade = new Trade(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: new NumericValueAsString('1'),
        closePrice: new NumericValueAsString('2'),
        size: new NumericValueAsString('1'),
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
        closeTime: Carbon::create(2024, 1, 1, 13),
    );

    expect($trade->isRealized())->toBeTrue();
});

it('builds a trade from scalars via make', function () {
    $trade = Trade::make(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: '45000',
        closePrice: '45500',
        size: '0.1',
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
        commission: 15,
        closeTime: Carbon::create(2024, 1, 1, 14),
    );

    expect($trade)
        ->toBeInstanceOf(Trade::class)
        ->and($trade->openPrice)->toBeInstanceOf(NumericValueAsString::class)
        ->and($trade->openPrice->toRawString())->toBe('45000.0000000000')
        ->and($trade->commission)->toBeInstanceOf(NumericValueAsString::class)
        ->and($trade->isRealized())->toBeTrue();
});

it('parses a string direction and time through make', function () {
    $trade = Trade::make(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: '1',
        closePrice: '2',
        size: '1',
        direction: 'sell',
        openTime: '2024-01-01 12:00:00',
    );

    expect($trade->direction)->toBe(Direction::SELL)
        ->and($trade->openTime->toDateTimeString())->toBe('2024-01-01 12:00:00')
        ->and($trade->commission)->toBeNull()
        ->and($trade->isOpen())->toBeTrue();
});

it('builds a trade from an array', function () {
    $trade = Trade::fromArray([
        'base_currency' => 'ETH',
        'quote_currency' => 'USD',
        'open_price' => '3000',
        'close_price' => '3200',
        'size' => '1.5',
        'direction' => 'buy',
        'open_time' => '2024-02-10 09:15:00',
        'commission' => '10',
        'close_time' => '2024-02-10 10:00:00',
    ]);

    expect($trade)
        ->toBeInstanceOf(Trade::class)
        ->and($trade->pair())->toBe('ETH/USD')
        ->and($trade->direction)->toBe(Direction::BUY)
        ->and($trade->isRealized())->toBeTrue();
});

it('throws when a required array key is missing', function (string $missing) {
    $attributes = [
        'base_currency' => 'BTC',
        'quote_currency' => 'USD',
        'open_price' => '1',
        'close_price' => '2',
        'size' => '1',
        'direction' => 'buy',
        'open_time' => '2024-01-01 12:00:00',
    ];

    unset($attributes[$missing]);

    expect(fn () => Trade::fromArray($attributes))
        ->toThrow(InvalidTradeException::class, "missing the required '{$missing}'");
})->with([
    'base_currency',
    'quote_currency',
    'open_price',
    'close_price',
    'size',
    'direction',
    'open_time',
]);

it('throws on an invalid direction string', function () {
    expect(fn () => Trade::make(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: '1',
        closePrice: '2',
        size: '1',
        direction: 'long',
        openTime: Carbon::create(2024, 1, 1, 12),
    ))->toThrow(InvalidTradeException::class, 'not a valid trade direction');
});

it('lazily collects rows into trades', function () {
    $collection = Trade::collect([
        [
            'base_currency' => 'BTC',
            'quote_currency' => 'USD',
            'open_price' => '45000',
            'close_price' => '45500',
            'size' => '0.1',
            'direction' => 'buy',
            'open_time' => '2024-01-01 12:00:00',
        ],
        Trade::make(
            baseCurrency: 'ETH',
            quoteCurrency: 'USD',
            openPrice: '3000',
            closePrice: '3200',
            size: '1',
            direction: Direction::SELL,
            openTime: Carbon::create(2024, 1, 2, 12),
        ),
    ]);

    expect($collection)->toBeInstanceOf(LazyCollection::class);

    $trades = $collection->all();

    expect($trades)->toHaveCount(2)
        ->and($trades[0])->toBeInstanceOf(Trade::class)
        ->and($trades[0]->pair())->toBe('BTC/USD')
        ->and($trades[1])->toBeInstanceOf(Trade::class)
        ->and($trades[1]->pair())->toBe('ETH/USD');
});

it('still validates currencies and close-before-open through the named constructors', function () {
    expect(fn () => Trade::make(
        baseCurrency: '',
        quoteCurrency: 'USD',
        openPrice: '1',
        closePrice: '2',
        size: '1',
        direction: Direction::BUY,
        openTime: Carbon::create(2024, 1, 1, 12),
    ))->toThrow(InvalidTradeException::class);

    expect(fn () => Trade::fromArray([
        'base_currency' => 'BTC',
        'quote_currency' => 'USD',
        'open_price' => '1',
        'close_price' => '2',
        'size' => '1',
        'direction' => 'buy',
        'open_time' => '2024-01-01 12:00:00',
        'close_time' => '2024-01-01 11:00:00',
    ]))->toThrow(InvalidTradeException::class, 'close time cannot be before');
});

it('serializes a trade to json', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade->toJson())->toBe(json_encode($trade->toArray()))
        ->and(json_encode($trade))->toBe($trade->toJson());
})->with('closed-with-returns-40-20-15');

it('takes any DateTimeInterface and a host string-backed enum through make', function () {
    $trade = Trade::make(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: '1',
        closePrice: '2',
        size: '1',
        direction: Side::Short,
        openTime: new DateTimeImmutable('2024-01-01 12:00:00'),
        closeTime: CarbonImmutable::parse('2024-01-01 13:00:00'),
    );

    expect($trade->direction)->toBe(Direction::SELL)
        ->and($trade->openTime)->toBeInstanceOf(Carbon::class)
        ->and($trade->openTime->toDateTimeString())->toBe('2024-01-01 12:00:00')
        ->and($trade->closeTime)->toBeInstanceOf(Carbon::class);
});

it('refuses an enum whose value is not a direction', function () {
    expect(fn () => Trade::make(
        baseCurrency: 'BTC',
        quoteCurrency: 'USD',
        openPrice: '1',
        closePrice: '2',
        size: '1',
        direction: Period::DAILY,
        openTime: '2024-01-01 12:00:00',
    ))->toThrow(InvalidTradeException::class, "'daily' is not a valid trade direction");
});
