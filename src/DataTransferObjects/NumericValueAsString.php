<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\Exceptions\DivisionByZeroException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidNumericOperationException;
use RoundlyConsulting\TradingAnalytics\Traits\HasPrefix;
use RoundlyConsulting\TradingAnalytics\Traits\HasScale;
use RoundlyConsulting\TradingAnalytics\Traits\HasSuffix;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;
use Stringable;

/** @implements Arrayable<string, mixed> */
final class NumericValueAsString implements Arrayable, Jsonable, JsonSerializable, Stringable
{
    use HasPrefix;
    use HasScale;
    use HasSuffix;
    use SerializesToJson;

    /** @var numeric-string */
    protected string $value = '0.0000000000';

    protected bool $hasBeenChanged = false;

    public function __construct(string|int|float|NumericValueAsString $value = '0', int $scale = 10, bool $hasBeenChanged = false, string $prefix = '', string $suffix = '')
    {
        $this->scale($scale)
            ->set($value)
            ->prefix($prefix)
            ->suffix($suffix);

        $this->hasBeenChanged = $hasBeenChanged;
    }

    /**
     * Named constructor — reads cleaner than `new NumericValueAsString(...)` in
     * consumer code and the README.
     */
    public static function of(string|int|float|NumericValueAsString $value = '0', int $scale = 10, string $prefix = '', string $suffix = ''): self
    {
        return new self(value: $value, scale: $scale, prefix: $prefix, suffix: $suffix);
    }

    /**
     * Return a clone with the given display prefix set, leaving the original
     * value untouched (immutable formatting helper).
     */
    public function withPrefix(string $prefix): self
    {
        $clone = $this->clone();
        $clone->prefix($prefix);
        $clone->suffix($this->suffix);

        return $clone;
    }

    /**
     * Return a clone with the given display suffix set, leaving the original
     * value untouched (immutable formatting helper).
     */
    public function withSuffix(string $suffix): self
    {
        $clone = $this->clone();
        $clone->prefix($this->prefix);
        $clone->suffix($suffix);

        return $clone;
    }

    public function set(string|int|float|NumericValueAsString $value, ?int $scale = null): self
    {
        if (! is_null($scale)) {
            $this->scale($scale);
        }

        $this->resolveMutation(
            $this->value($value)
        );

        return $this;
    }

    public function add(string|int|float|NumericValueAsString $value, bool $immutable = false): self
    {
        $result = bcadd($this->value, $this->value($value), $this->scale);

        return $this->resolveMutation($result, $immutable, true);
    }

    public function subtract(string|int|float|NumericValueAsString $value, bool $immutable = false): self
    {
        $result = bcsub($this->value, $this->value($value), $this->scale);

        return $this->resolveMutation($result, $immutable, true);
    }

    public function multiply(string|int|float|NumericValueAsString $value, bool $immutable = false): self
    {
        $result = bcmul($this->value, $this->value($value), $this->scale);

        return $this->resolveMutation($result, $immutable, true);
    }

    public function divide(string|int|float|NumericValueAsString $value, bool $immutable = false): self
    {
        $divisor = $this->value($value);

        if (bccomp($divisor, '0', $this->scale) === 0) {
            throw DivisionByZeroException::make();
        }

        $result = bcdiv($this->value, $divisor, $this->scale);

        return $this->resolveMutation($result, $immutable, true);
    }

    public function pow(string|int|float $exponent, bool $immutable = false): self
    {
        $normalized = $this->value($exponent);

        if (bccomp($normalized, bcadd($normalized, '0', 0), $this->scale) !== 0) {
            throw InvalidNumericOperationException::fractionalExponent((string) $exponent);
        }

        $result = bcpow($this->value, $normalized, $this->scale);

        return $this->resolveMutation($result, $immutable, true);
    }

    public function abs(bool $immutable = false): self
    {
        $result = $this->isLessThan(0)
            ? bcmul($this->value, '-1', $this->scale)
            : $this->value;

        return $this->resolveMutation($result, $immutable, true);
    }

    public function isNonZero(): bool
    {
        return ! $this->isZero();
    }

    public function isZero(): bool
    {
        return $this->equals(0);
    }

    public function isPositiveNonZero(): bool
    {
        return $this->isGreaterThan(0);
    }

    public function isGreaterThan(string|int|float|NumericValueAsString $value): bool
    {
        return bccomp($this->value, $this->value($value), $this->scale) === 1;
    }

    public function isLessThan(string|int|float|NumericValueAsString $value): bool
    {
        return bccomp($this->value, $this->value($value), $this->scale) === -1;
    }

    public function isGreaterThanOrEqualTo(string|int|float|NumericValueAsString $value): bool
    {
        return bccomp($this->value, $this->value($value), $this->scale) >= 0;
    }

    public function isLessThanOrEqualTo(string|int|float|NumericValueAsString $value): bool
    {
        return bccomp($this->value, $this->value($value), $this->scale) <= 0;
    }

    public function equals(string|int|float|NumericValueAsString $value): bool
    {
        return bccomp($this->value, $this->value($value), $this->scale) === 0;
    }

    public function clone(): self
    {
        return new self(
            value: $this->value,
            scale: $this->scale,
            hasBeenChanged: $this->hasBeenChanged,
        );
    }

    public function cloneWithScale(int $scale): self
    {
        return new self(
            value: $this->value,
            scale: $scale,
            hasBeenChanged: $this->hasBeenChanged,
        );
    }

    public function round(int $scale): self
    {
        $rounded = $this->roundValue($this->value, $scale);

        $this->scale($scale);

        return $this->resolveMutation($rounded);
    }

    /**
     * Round a numeric string half away from zero to the given scale.
     *
     * bcmath only ever truncates, so we shift a signed 0.5-at-scale increment
     * into the value before truncating to make the rounding direction correct.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    protected function roundValue(string $value, int $scale): string
    {
        $half = bcdiv('1', bcpow('10', (string) $scale, $scale + 1), $scale + 1);
        $half = bcdiv($half, '2', $scale + 1);

        $signedHalf = bccomp($value, '0', $scale + 1) < 0
            ? bcmul($half, '-1', $scale + 1)
            : $half;

        return bcadd(bcadd($value, $signedHalf, $scale + 1), '0', $scale);
    }

    public function wasChanged(): bool
    {
        return $this->hasBeenChanged;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return array{value: numeric-string, scale: int, prefix: string, suffix: string, formatted: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'scale' => $this->scale,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'formatted' => $this->toString(),
        ];
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function toString(): string
    {
        $string = [];

        if ($this->hasPrefix()) {
            $string[] = $this->prefix;
        }

        $string[] = $this->value;

        if ($this->hasSuffix()) {
            $string[] = $this->suffix;
        }

        return implode(' ', $string);
    }

    /** @return numeric-string */
    public function toRawString(): string
    {
        return $this->value;
    }

    /** @param numeric-string $value */
    protected function resolveMutation(string $value, bool $immutable = false, bool $hasBeenChanged = false): self
    {
        if ($immutable) {
            return new self(
                value: $value,
                scale: $this->scale,
                hasBeenChanged: $hasBeenChanged
            );
        }

        $this->value = $value;
        $this->hasBeenChanged = $hasBeenChanged;

        return $this;
    }

    /** @return numeric-string */
    protected function value(string|int|float|NumericValueAsString $value): string
    {
        $value = $value instanceof NumericValueAsString ? $value->toRawString() : (string) $value;

        if (! is_numeric($value)) {
            throw InvalidNumericOperationException::nonNumericValue($value);
        }

        return bcadd($value, '0', $this->scale);
    }
}
