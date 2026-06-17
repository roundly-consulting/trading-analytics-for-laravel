<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidScaleException;

it('returns the configured scale', function () {
    $value = new NumericValueAsString('1', scale: 6);

    expect($value->getScale())->toBe(6);

    $value->scale(3);

    expect($value->getScale())->toBe(3);
});

it('rejects a negative scale', function () {
    expect(fn () => new NumericValueAsString('1', scale: -1))
        ->toThrow(InvalidScaleException::class);
});

it('rejects a negative scale set after construction', function () {
    $value = new NumericValueAsString('1', scale: 2);

    expect(fn () => $value->scale(-5))->toThrow(InvalidScaleException::class);
});
