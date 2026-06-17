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

        // Seed the guess with 1; Newton's method converges quickly for our inputs.
        $guess = '1';

        for ($iteration = 0; $iteration < 100; $iteration++) {
            $power = bcpow($guess, $previousExponent, $workScale);

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
}
