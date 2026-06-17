<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;

it('omits pairs whose total is zero from the array', function () {
    $dto = new NumericDirectionalAggregatesByCurrency(scale: 2);

    // Touching forPair() materialises an all-zero aggregate for the pair.
    $dto->forPair('BTC/USD');

    // A second pair with a non-zero total should be the only one serialised.
    $dto->forPair('ETH/USD')->total->total->add(5);

    $array = $dto->toArray();

    expect($array['per_pair'])
        ->toHaveKey('ETH/USD')
        ->not->toHaveKey('BTC/USD');
});
