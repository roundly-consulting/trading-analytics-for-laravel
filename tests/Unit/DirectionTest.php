<?php

declare(strict_types=1);

use RoundlyConsulting\Enums\Exceptions\EnumException;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;

it('identifies buy direction', function () {
    expect(Direction::BUY->isBuy())->toBeTrue()
        ->and(Direction::BUY->isSell())->toBeFalse();
});

it('identifies sell direction', function () {
    expect(Direction::SELL->isSell())->toBeTrue()
        ->and(Direction::SELL->isBuy())->toBeFalse();
});

it('returns the direction values as a collection', function () {
    expect(Direction::values()->all())->toBe(['buy', 'sell']);
});

it('exposes readable labels', function () {
    expect(Direction::labels()->all())->toBe(['Buy', 'Sell']);
});

it('reads each case cleanly', function () {
    expect(Direction::BUY->readable())->toBe('Buy')
        ->and(Direction::SELL->readable())->toBe('Sell');
});

it('builds a value => label option map', function () {
    expect(Direction::toOptions()->all())->toBe([
        'buy' => 'Buy',
        'sell' => 'Sell',
    ]);
});

it('builds a validation rule', function () {
    expect(Direction::validationRule())->toBe('in:buy,sell');
});

it('resolves a case from its label', function () {
    expect(Direction::fromLabel('Sell'))->toBe(Direction::SELL);
});

it('throws resolving an unknown name', function () {
    Direction::fromName('nope');
})->throws(EnumException::class);

it('returns null resolving an unknown value', function () {
    expect(Direction::tryFrom('hold'))->toBeNull();
});
