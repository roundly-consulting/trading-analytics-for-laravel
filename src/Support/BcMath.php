<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Support;

use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidNumericOperationException;

/**
 * Arbitrary-precision helpers that the bcmath extension does not provide
 * out of the box, kept free of float arithmetic so the package's precision
 * guarantee holds end to end.
 */
final class BcMath
{
    /** Guard digits on top of the digits the result needs, so truncation never reaches them. */
    private const int GUARD_DIGITS = 10;

    /**
     * Compute the nth root of a non-negative value using Newton's method,
     * entirely in bcmath so no float precision is lost.
     *
     * The value is split into a mantissa in [1, 10) and a power of ten, `exponent = q·n + r`
     * with 0 ≤ r < n, so the root is `mantissa^(1/n) × (10^(1/n))^r × 10^q`: every Newton
     * iteration runs on a value between 1 and 10, whatever the magnitude of the input. A value
     * below 10^-scale used to read as 0, and a value far below 1, started from 1, shrank by only
     * (n − 1) / n per step and stopped 100 steps short of its root.
     *
     * Memory and time stay flat in `$n`: the iterate is raised with {@see power()}, which
     * keeps every intermediate at a fixed scale, where `bcpow()` would carry
     * `$n` × scale digits through its squarings (the geometric mean of 50,000 trades built a
     * ~1.25M-digit intermediate).
     *
     * The result is truncated to `$scale`; a root that lies within 5 × 10^-(scale + 7) below a
     * digit boundary (0.5 itself, computed as 0.4999…) reads as the boundary.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    public static function nthRoot(string $value, int $n, int $scale = 20): string
    {
        if ($n < 1) {
            throw InvalidNumericOperationException::fractionalExponent((string) $n);
        }

        $sign = self::sign($value);

        if ($sign < 0) {
            throw InvalidNumericOperationException::nonNumericValue($value);
        }

        if ($sign === 0) {
            return bcadd('0', '0', $scale);
        }

        if ($n === 1) {
            return bcadd($value, '0', $scale);
        }

        [$mantissa, $exponent] = self::scientific($value);

        $q = intdiv($exponent, $n);
        $r = $exponent % $n;

        if ($r < 0) {
            $r += $n;
            $q--;
        }

        // The root is below 10^(q + 1) ≤ 10^-(scale + 1): 0 at this scale.
        if ($q <= -$scale - 2) {
            return bcadd('0', '0', $scale);
        }

        // The digits the [1, 10) part needs once shifted by q, plus guard digits for the r-fold
        // power, whose error grows with r < n.
        $workScale = max($scale + $q, 0) + self::GUARD_DIGITS + strlen((string) $n);

        $root = self::newton($mantissa, $n, $workScale);

        if ($r > 0) {
            $root = bcmul($root, self::power(self::newton('10', $n, $workScale), $r, $workScale), $workScale);
        }

        $root = self::shift($root, $q);

        // Half a unit of the (scale + 6)th decimal lifts a root computed a hair below a digit
        // boundary back onto it before the truncation.
        $nudge = bcmul('5', bcpow('10', (string) (-$scale - 7), $scale + 7), $scale + 7);

        /** @var numeric-string $result */
        $result = bcadd($root, $nudge, $scale);

        return $result;
    }

    /**
     * Newton's method for the nth root of a value between 1 and 10, to `$scale` decimals.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    private static function newton(string $value, int $n, int $scale): string
    {
        // Work a few digits beyond the requested scale for a stable iteration.
        $workScale = $scale + 5;
        $exponent = (string) $n;
        $previousExponent = (string) ($n - 1);

        $guess = self::seed($value, $n, $workScale);

        for ($iteration = 0; $iteration < 100; $iteration++) {
            // The iterate stays at or above 1, so its power is never 0.
            $power = self::power($guess, $n - 1, $workScale);

            // next = ((n - 1) * guess + value / guess^(n-1)) / n
            $next = bcdiv(
                bcadd(
                    bcmul($previousExponent, $guess, $workScale),
                    bcdiv($value, $power, $workScale),
                    $workScale
                ),
                $exponent,
                $workScale
            );

            if (bccomp($next, $guess, $scale) === 0) {
                $guess = $next;

                break;
            }

            $guess = $next;
        }

        /** @var numeric-string $result */
        $result = bcadd($guess, '0', $scale);

        return $result;
    }

    /**
     * The starting point for {@see newton()}, just above the root so Newton's method descends
     * onto it monotonically.
     *
     * Started from 1, the descent from a root well above it shrinks the iterate by only a factor
     * of (n − 1) / n per step — 100 steps could not reach the root of a 1,000× growth over 1,000
     * trades. A float estimate, nudged up a hair, lands the start next to the root instead; the
     * digits still all come from the bcmath iteration. The value is between 1 and 10, so the
     * estimate is always finite.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    private static function seed(string $value, int $n, int $scale): string
    {
        $estimate = 10 ** (log10((float) $value) / $n) * (1 + 1e-12);

        /** @var numeric-string $seed */
        $seed = sprintf('%.'.min($scale, 50).'F', $estimate);

        return $seed;
    }

    /**
     * A positive value as a mantissa in [1, 10) and the power of ten it is scaled by, read off
     * its digits: `0.00123` is `[1.23, -3]`.
     *
     * @param  numeric-string  $value
     * @return array{numeric-string, int}
     */
    private static function scientific(string $value): array
    {
        $exponent = self::exponent($value);
        [$integer, $fraction] = array_pad(explode('.', ltrim($value, '+'), 2), 2, '');
        $digits = ltrim($integer.$fraction, '0');

        /** @var numeric-string $mantissa */
        $mantissa = sprintf('%s.%s', $digits[0], substr($digits, 1) ?: '0');

        return [$mantissa, $exponent];
    }

    /**
     * The power of ten of a non-zero value's leading digit — floor(log10(|value|)) — read off its
     * digits, so it is exact for any magnitude.
     *
     * @internal
     *
     * @param  numeric-string  $value
     */
    public static function exponent(string $value): int
    {
        [$integer, $fraction] = array_pad(explode('.', ltrim($value, '+-'), 2), 2, '');
        $integer = ltrim($integer, '0');

        if ($integer !== '') {
            return strlen($integer) - 1;
        }

        return strlen(ltrim($fraction, '0')) - strlen($fraction) - 1;
    }

    /**
     * Multiply a value by 10^places, exactly, by moving its decimal point — no division by a
     * power of ten that has as many digits as the shift.
     *
     * @internal
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    public static function shift(string $value, int $places): string
    {
        $sign = str_starts_with($value, '-') ? '-' : '';
        [$integer, $fraction] = array_pad(explode('.', ltrim($value, '+-'), 2), 2, '');

        if ($places >= 0) {
            $fraction = str_pad($fraction, $places, '0');
            $integer .= substr($fraction, 0, $places);
            $fraction = substr($fraction, $places);
        } else {
            $integer = str_pad($integer, -$places + 1, '0', STR_PAD_LEFT);
            $fraction = substr($integer, $places).$fraction;
            $integer = substr($integer, 0, $places);
        }

        $integer = ltrim($integer, '0');

        /** @var numeric-string $shifted */
        $shifted = sprintf('%s%s%s', $sign, $integer === '' ? '0' : $integer, $fraction === '' ? '' : '.'.$fraction);

        return $shifted;
    }

    /**
     * The sign of a value, compared at its own decimals: compared at an output scale, a value
     * below 10^-scale read as 0.
     *
     * @param  numeric-string  $value
     */
    private static function sign(string $value): int
    {
        $point = strpos($value, '.');

        return bccomp($value, '0', $point === false ? 0 : strlen($value) - $point - 1);
    }

    /**
     * `$base` raised to a non-negative integer power by square-and-multiply, every product
     * truncated to `$scale` plus guard digits so the operands never outgrow the scale.
     *
     * @param  numeric-string  $base
     * @return numeric-string
     */
    private static function power(string $base, int $exponent, int $scale): string
    {
        $guardScale = 2 * $scale;
        $result = '1';

        while ($exponent > 0) {
            if (($exponent & 1) === 1) {
                $result = bcmul($result, $base, $guardScale);
            }

            $exponent >>= 1;

            if ($exponent > 0) {
                $base = bcmul($base, $base, $guardScale);
            }
        }

        /** @var numeric-string $truncated */
        $truncated = bcadd($result, '0', $scale);

        return $truncated;
    }

    /**
     * Square root of a non-negative value, kept in bcmath.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    public static function sqrt(string $value, int $scale = 20): string
    {
        if (self::sign($value) <= 0) {
            return bcadd('0', '0', $scale);
        }

        /** @var numeric-string $result */
        $result = bcsqrt($value, $scale);

        return $result;
    }
}
