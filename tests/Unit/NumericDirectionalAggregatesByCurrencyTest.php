<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;

it('omits keys no trade fed from the array', function () {
    $dto = new NumericDirectionalAggregatesByCurrency(scale: 2);

    // Touching forPair() materialises an empty aggregate for the pair.
    $dto->forPair('BTC/USD');

    $dto->forPair('ETH/USD')->total->record(NumericValueAsString::of(5), 'ETH/USD');

    expect($dto->toArray()['per_pair'])
        ->toHaveKey('ETH/USD')
        ->not->toHaveKey('BTC/USD');
});

it('keeps a key whose recorded values net to zero', function () {
    $dto = new NumericDirectionalAggregatesByCurrency(scale: 2);

    $dto->forPair('BTC/USD')->total->record(NumericValueAsString::of(10), 'BTC/USD');
    $dto->forPair('BTC/USD')->total->record(NumericValueAsString::of(-10), 'BTC/USD');

    expect($dto->toArray()['per_pair'])->toHaveKey('BTC/USD')
        ->and($dto->forPair('BTC/USD')->total->count)->toBe(2)
        ->and($dto->forPair('BTC/USD')->total->total->toRawString())->toBe('0.00');
});
