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
    /**
     * Compute the nth root of a non-negative value using Newton's method,
     * entirely in bcmath so no float precision is lost.
     *
     * Memory and time stay flat in `$n`: the iterate is raised with {@see power()}, which
     * keeps every intermediate at a fixed scale, where `bcpow()` would carry
     * `$n` × scale digits through its squarings (the geometric mean of 50,000 trades built a
     * ~1.25M-digit intermediate).
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    public static function nthRoot(string $value, int $n, int $scale = 20): string
    {
        if ($n < 1) {
            throw InvalidNumericOperationException::fractionalExponent((string) $n);
        }

        if (bccomp($value, '0', $scale) < 0) {
            throw InvalidNumericOperationException::nonNumericValue($value);
        }

        if (bccomp($value, '0', $scale) === 0) {
            return bcadd('0', '0', $scale);
        }

        if ($n === 1) {
            return bcadd($value, '0', $scale);
        }

        // Work a few digits beyond the requested scale for a stable iteration.
        $workScale = $scale + 5;
        $exponent = (string) $n;
        $previousExponent = (string) ($n - 1);

        $guess = self::seed($value, $n, $workScale);

        for ($iteration = 0; $iteration < 100; $iteration++) {
            $power = self::power($guess, $n - 1, $workScale);

            if (bccomp($power, '0', $workScale) === 0) {
                break;
            }

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
     * The starting point for {@see nthRoot()}, always at or above the root so Newton's method
     * descends onto it monotonically.
     *
     * A value up to 1 starts from 1, where the descent takes a bounded number of steps. Above
     * 1 that start overshoots to about `value / n`, and the descent from there shrinks the
     * iterate by only a factor of (n − 1) / n per step — 100 steps could not reach the root
     * of a 1,000× growth over 1,000 trades. A float estimate, nudged up a hair, lands the
     * start next to the root instead; the digits still all come from the bcmath iteration.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    private static function seed(string $value, int $n, int $scale): string
    {
        if (bccomp($value, '1', $scale) <= 0) {
            return '1';
        }

        $asFloat = (float) $value;

        // Beyond float range the magnitude comes from the digit count of the integer part.
        $log10 = is_finite($asFloat)
            ? log10($asFloat)
            : strlen(ltrim(explode('.', $value)[0], '+0')) - 1;

        $estimate = 10 ** ($log10 / $n) * (1 + 1e-12);

        if (! is_finite($estimate)) {
            // A power of ten one above the root's magnitude: still an upper bound, and this
            // only happens for small n, where the descent is quick.
            return bcpow('10', (string) ((int) ceil($log10 / $n) + 1));
        }

        /** @var numeric-string $seed */
        $seed = sprintf('%.'.min($scale, 50).'F', $estimate);

        return $seed;
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
        if (bccomp($value, '0', $scale) <= 0) {
            return bcadd('0', '0', $scale);
        }

        /** @var numeric-string $result */
        $result = bcsqrt($value, $scale);

        return $result;
    }
}
