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

it('keeps a 50,000th root fast and flat in memory', function () {
    memory_reset_peak_usage();
    $baseline = memory_get_usage();

    // bcpow() carried n × scale digits: this root built a ~1.25M-digit intermediate and took
    // ~17s. Every digit below is checked against an independent 60-digit computation.
    expect(BcMath::nthRoot('2', 50_000))->toBe('1.00001386303970224572')
        ->and(memory_get_peak_usage() - $baseline)->toBeLessThan(256 * 1024);
});

it('converges on the root of a large growth factor', function () {
    // Seeded at 1, the descent from value / n shrank by (n − 1) / n per step and stopped at
    // 100 steps far above the root: 906.6… for 1,000,000^(1/1000), 1.0406… for 150^(1/1000).
    expect(BcMath::nthRoot('1000000', 1000))->toBe('1.01391138573667941199')
        ->and(BcMath::nthRoot('150', 1000))->toBe('1.00502320951996924605')
        ->and(BcMath::nthRoot('1.5', 3))->toBe('1.14471424255333186780');
});

it('seeds a root of a value beyond float range from its digit count', function () {
    $huge = '1'.str_repeat('0', 400); // 10^400: (float) is INF

    expect(BcMath::nthRoot($huge, 400, 10))->toBe('10.0000000000')
        ->and(BcMath::nthRoot('1'.str_repeat('0', 700), 2, 4))->toBe('1'.str_repeat('0', 350).'.0000');
});

it('roots a value below 10^-scale and a root far below 1', function () {
    // Compared to 0 at the output scale, 1e-24 read as 0; and a value far below 1, started
    // from 1, shrank by only (n − 1) / n per step and stopped at 100 steps nowhere near 0.5.
    expect(BcMath::nthRoot('0.000000000000000000000001', 2))->toBe('0.00000000000100000000')
        ->and(BcMath::sqrt('0.000000000000000000000001'))->toBe('0.00000000000100000000')
        ->and(BcMath::nthRoot(bcpow('0.5', '1000', 400), 1000))->toBe('0.50000000000000000000');
});
