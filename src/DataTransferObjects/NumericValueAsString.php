<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use RoundlyConsulting\TradingAnalytics\Traits\HasPrefix;
use RoundlyConsulting\TradingAnalytics\Traits\HasScale;
use RoundlyConsulting\TradingAnalytics\Traits\HasSuffix;
use Stringable;

class NumericValueAsString implements Stringable
{
    use HasPrefix;
    use HasScale;
    use HasSuffix;

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
        $result = bcdiv($this->value, $this->value($value), $this->scale);

        return $this->resolveMutation($result, $immutable, true);
    }

    public function pow(string|int|float $exponent, bool $immutable = false): self
    {
        $result = bcpow($this->value, $this->value($exponent), $this->scale);

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
        return $this->isGreaterThan(0);
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
        $this->scale($scale);

        $value = $this->value($this->value);

        return $this->resolveMutation(
            $value,
        );
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

        $numeric = is_numeric($value) ? $value : '0';

        return bcadd($numeric, '0', $this->scale);
    }
}
