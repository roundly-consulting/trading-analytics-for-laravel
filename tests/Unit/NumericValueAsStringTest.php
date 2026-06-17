<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\Exceptions\DivisionByZeroException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidNumericOperationException;

it('reports negative values as non-zero', function () {
    expect((new NumericValueAsString(-5))->isNonZero())->toBeTrue()
        ->and((new NumericValueAsString(0))->isNonZero())->toBeFalse()
        ->and((new NumericValueAsString(5))->isNonZero())->toBeTrue();
});

it('distinguishes positive non-zero from negative', function () {
    expect((new NumericValueAsString(-5))->isPositiveNonZero())->toBeFalse()
        ->and((new NumericValueAsString(5))->isPositiveNonZero())->toBeTrue()
        ->and((new NumericValueAsString(0))->isPositiveNonZero())->toBeFalse();
});

it('throws when dividing by zero', function () {
    expect(fn () => (new NumericValueAsString(10))->divide(0))
        ->toThrow(DivisionByZeroException::class);
});

it('divides non-zero divisors', function () {
    expect((new NumericValueAsString(10, scale: 2))->divide(4)->toRawString())->toBe('2.50');
});

it('rounds half away from zero', function (string $value, int $scale, string $expected) {
    expect((new NumericValueAsString($value, scale: 10))->round($scale)->toRawString())->toBe($expected);
})->with([
    ['1.005', 2, '1.01'],
    ['-1.005', 2, '-1.01'],
    ['2.4', 0, '2'],
    ['2.5', 0, '3'],
    ['-2.5', 0, '-3'],
    ['1.004', 2, '1.00'],
]);

it('rejects fractional exponents in pow', function () {
    expect(fn () => (new NumericValueAsString('2', scale: 10))->pow('2.5'))
        ->toThrow(InvalidNumericOperationException::class);
});

it('powers integer exponents correctly', function () {
    expect((new NumericValueAsString('3', scale: 2))->pow('3')->toRawString())->toBe('27.00')
        ->and((new NumericValueAsString('2', scale: 0))->pow('10')->toRawString())->toBe('1024');
});

it('rejects non-numeric input', function (string $value) {
    expect(fn () => new NumericValueAsString($value))
        ->toThrow(InvalidNumericOperationException::class);
})->with(['abc', '12abc', 'NaN', '1,000', '']);

it('adds, subtracts and multiplies', function () {
    $value = new NumericValueAsString('10', scale: 2);

    expect($value->add(5)->toRawString())->toBe('15.00')
        ->and($value->subtract(3)->toRawString())->toBe('12.00')
        ->and($value->multiply(2)->toRawString())->toBe('24.00');
});

it('returns the absolute value for positive and negative inputs', function () {
    expect((new NumericValueAsString('-7.50', scale: 2))->abs()->toRawString())->toBe('7.50')
        ->and((new NumericValueAsString('7.50', scale: 2))->abs()->toRawString())->toBe('7.50');
});

it('compares values across every operator', function () {
    $five = new NumericValueAsString('5', scale: 2);

    expect($five->isGreaterThan(4))->toBeTrue()
        ->and($five->isLessThan(6))->toBeTrue()
        ->and($five->isGreaterThanOrEqualTo(5))->toBeTrue()
        ->and($five->isGreaterThanOrEqualTo(6))->toBeFalse()
        ->and($five->isLessThanOrEqualTo(5))->toBeTrue()
        ->and($five->isLessThanOrEqualTo(4))->toBeFalse()
        ->and($five->equals(5))->toBeTrue()
        ->and($five->isZero())->toBeFalse();
});

it('clones a value and a value with a new scale', function () {
    $value = new NumericValueAsString('5.1234', scale: 4);

    expect($value->clone()->toRawString())->toBe('5.1234')
        ->and($value->cloneWithScale(2)->toRawString())->toBe('5.12');
});

it('formats with a prefix and a suffix', function () {
    $value = new NumericValueAsString('5.00', scale: 2, prefix: '$', suffix: 'USD');

    expect($value->toString())->toBe('$ 5.00 USD')
        ->and((string) $value)->toBe('$ 5.00 USD');
});

it('formats with only a suffix', function () {
    $value = new NumericValueAsString('5.00', scale: 2, suffix: 'per day');

    expect($value->toString())->toBe('5.00 per day');
});

it('exposes integer, float and raw representations', function () {
    $value = new NumericValueAsString('5.99', scale: 2);

    expect($value->toInt())->toBe(5)
        ->and($value->toFloat())->toBe(5.99)
        ->and($value->toRawString())->toBe('5.99');
});

it('tracks whether a value was changed', function () {
    $value = new NumericValueAsString('5', scale: 2);

    expect($value->wasChanged())->toBeFalse();

    $value->add(1);

    expect($value->wasChanged())->toBeTrue();
});

it('exposes the value as an array', function () {
    $value = new NumericValueAsString('5.00', scale: 2, prefix: '$');

    expect($value->toArray())->toBe([
        'value' => '5.00',
        'scale' => 2,
        'prefix' => '$',
        'suffix' => '',
        'formatted' => '$ 5.00',
    ]);
});
