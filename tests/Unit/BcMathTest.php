<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidNumericOperationException;
use RoundlyConsulting\TradingAnalytics\Support\BcMath;

it('computes integer nth roots precisely', function () {
    expect(BcMath::nthRoot('8', 3, 2))->toBe('2.00')
        ->and(BcMath::nthRoot('16', 2, 2))->toBe('4.00')
        ->and(BcMath::nthRoot('1.93200000000000000000', 3, 10))->toBe('1.2454769869');
});

it('returns zero for the nth root of zero', function () {
    expect(BcMath::nthRoot('0', 3, 4))->toBe('0.0000');
});

it('returns the value for the first root', function () {
    expect(BcMath::nthRoot('5.25', 1, 2))->toBe('5.25');
});

it('rejects an nth root with n below one', function () {
    expect(fn () => BcMath::nthRoot('8', 0))
        ->toThrow(InvalidNumericOperationException::class);
});

it('rejects an nth root of a negative value', function () {
    expect(fn () => BcMath::nthRoot('-8', 3))
        ->toThrow(InvalidNumericOperationException::class);
});

it('computes square roots and clamps non-positive inputs to zero', function () {
    expect(BcMath::sqrt('9', 2))->toBe('3.00')
        ->and(BcMath::sqrt('0', 2))->toBe('0.00')
        ->and(BcMath::sqrt('-4', 2))->toBe('0.00');
});
