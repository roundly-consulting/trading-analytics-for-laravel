<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/**
 * @phpstan-type TradeRow array{
 *     base_currency: string,
 *     quote_currency: string,
 *     open_price: string|int|float|NumericValueAsString,
 *     close_price: string|int|float|NumericValueAsString,
 *     size: string|int|float|NumericValueAsString,
 *     direction: string|BackedEnum,
 *     open_time: string|DateTimeInterface,
 *     commission?: string|int|float|NumericValueAsString|null,
 *     close_time?: string|DateTimeInterface|null
 * }
 *
 * @implements Arrayable<string, mixed>
 */
final class Trade implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

    /** The fields a row must carry. */
    private const array REQUIRED_FIELDS = ['base_currency', 'quote_currency', 'open_price', 'close_price', 'size', 'direction', 'open_time'];

    /** Every field a row is read for. */
    private const array FIELDS = [...self::REQUIRED_FIELDS, 'commission', 'close_time'];

    /** The fewest decimals {@see make()} keeps an amount at — the package's default scale. */
    private const int MIN_AMOUNT_SCALE = 10;

    /**
     * The most decimals {@see make()} keeps an amount at: far past any real price or size, and
     * it stops a short exponent such as `1e-999999` from expanding into a string that long.
     */
    private const int MAX_AMOUNT_SCALE = NumericValueAsString::MAX_EXPONENT;

    public function __construct(
        public string $baseCurrency,
        public string $quoteCurrency,
        public NumericValueAsString $openPrice,
        public NumericValueAsString $closePrice,
        public NumericValueAsString $size,
        public Direction $direction,
        public Carbon $openTime,
        public ?NumericValueAsString $commission = null,
        public ?Carbon $closeTime = null,
    ) {
        if (trim($this->baseCurrency) === '') {
            throw InvalidTradeException::emptyCurrency('base currency');
        }

        if (trim($this->quoteCurrency) === '') {
            throw InvalidTradeException::emptyCurrency('quote currency');
        }

        if ($this->closeTime !== null && $this->closeTime->lessThan($this->openTime)) {
            throw InvalidTradeException::closeBeforeOpen();
        }

        // Every return divides by the value at entry (open price × size), and a negative size
        // would flip the P&L against the ROI. A close at 0 is a real total loss.
        if (! $this->size->isPositiveNonZero()) {
            throw InvalidTradeException::nonPositiveSize($this->size->toRawString());
        }

        if (! $this->openPrice->isPositiveNonZero()) {
            throw InvalidTradeException::nonPositiveOpenPrice($this->openPrice->toRawString());
        }

        if ($this->closePrice->isLessThan(0)) {
            throw InvalidTradeException::negativeClosePrice($this->closePrice->toRawString());
        }
    }

    /**
     * Build a trade from scalar values, wrapping the numeric fields in
     * {@see NumericValueAsString} for the caller so they never type
     * `new NumericValueAsString(...)` by hand.
     *
     * Every amount is kept exactly: at its own decimal places, and at least 10.
     *
     * The direction may be any string-backed enum whose value is `buy` or `sell` (a host's
     * own cast enum included); times may be any `DateTimeInterface` or a parseable string.
     */
    public static function make(
        string $baseCurrency,
        string $quoteCurrency,
        string|int|float|NumericValueAsString $openPrice,
        string|int|float|NumericValueAsString $closePrice,
        string|int|float|NumericValueAsString $size,
        BackedEnum|string $direction,
        DateTimeInterface|string $openTime,
        string|int|float|NumericValueAsString|null $commission = null,
        DateTimeInterface|string|null $closeTime = null,
    ): self {
        return new self(
            baseCurrency: $baseCurrency,
            quoteCurrency: $quoteCurrency,
            openPrice: self::amount($openPrice),
            closePrice: self::amount($closePrice),
            size: self::amount($size),
            direction: self::parseDirection($direction),
            openTime: self::parseTime($openTime),
            commission: $commission === null ? null : self::amount($commission),
            closeTime: self::parseNullableTime($closeTime),
        );
    }

    /**
     * Build a trade from an array of scalars (e.g. an API payload). Internals stay strictly
     * DTO-typed; for rows of any other shape use {@see fromRow()}.
     *
     * @param  TradeRow  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return self::fromAttributes($attributes);
    }

    /**
     * Ingestion boundary: build a trade from a row of any shape a Laravel app produces —
     * an array, a query-builder `stdClass` row, an Eloquent model (read attribute by
     * attribute, so its casts and accessors apply), any `Arrayable`, or a plain object's
     * public properties. A `Trade` is returned as is.
     *
     * A required field that is absent or null throws {@see InvalidTradeException}, as does
     * a field of the wrong type.
     *
     * @param  TradeRow|array<array-key, mixed>|object  $row
     */
    public static function fromRow(array|object $row): self
    {
        return match (true) {
            $row instanceof self => $row,
            is_array($row) => self::fromAttributes($row),
            $row instanceof Model => self::fromAttributes(self::modelAttributes($row)),
            $row instanceof Arrayable => self::fromAttributes($row->toArray()),
            default => self::fromAttributes(get_object_vars($row)),
        };
    }

    /**
     * Lazily map an iterable of rows (anything {@see fromRow()} reads, trades included) into
     * trades, so `Analytics::make(Trade::collect($query->orderBy('close_time')->orderBy('id')->lazy()))`
     * is a one-liner. Nothing is read until the collection is iterated.
     *
     * @param  iterable<Trade|TradeRow|array<array-key, mixed>|object>  $rows
     * @return LazyCollection<int, Trade>
     */
    public static function collect(iterable $rows): LazyCollection
    {
        return LazyCollection::make(static function () use ($rows): iterable {
            foreach ($rows as $row) {
                yield self::fromRow($row);
            }
        });
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     */
    private static function fromAttributes(array $attributes): self
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (($attributes[$field] ?? null) === null) {
                throw InvalidTradeException::missingField($field);
            }
        }

        return self::make(
            baseCurrency: self::text($attributes, 'base_currency'),
            quoteCurrency: self::text($attributes, 'quote_currency'),
            openPrice: self::number($attributes, 'open_price'),
            closePrice: self::number($attributes, 'close_price'),
            size: self::number($attributes, 'size'),
            direction: self::directionField($attributes),
            openTime: self::time($attributes, 'open_time'),
            commission: ($attributes['commission'] ?? null) === null ? null : self::number($attributes, 'commission'),
            closeTime: ($attributes['close_time'] ?? null) === null ? null : self::time($attributes, 'close_time'),
        );
    }

    /**
     * The trade fields of a model, read through `getAttribute()` so casts and accessors
     * apply. A field the model neither holds (unselected, say) nor exposes via an accessor
     * is left out rather than read, which also keeps `preventAccessingMissingAttributes()`
     * from firing on an optional column.
     *
     * @return array<string, mixed>
     */
    private static function modelAttributes(Model $model): array
    {
        $stored = $model->getAttributes();
        $attributes = [];

        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $stored) || $model->hasGetMutator($field) || $model->hasAttributeGetMutator($field)) {
                $attributes[$field] = $model->getAttribute($field);
            }
        }

        return $attributes;
    }

    /** @param array<array-key, mixed> $attributes */
    private static function text(array $attributes, string $field): string
    {
        $value = $attributes[$field];

        return is_string($value) ? $value : throw InvalidTradeException::invalidField($field, 'a string', $value);
    }

    /** @param array<array-key, mixed> $attributes */
    private static function number(array $attributes, string $field): string|int|float|NumericValueAsString
    {
        $value = $attributes[$field];

        return is_string($value) || is_int($value) || is_float($value) || $value instanceof NumericValueAsString
            ? $value
            : throw InvalidTradeException::invalidField($field, 'a number or numeric string', $value);
    }

    /** @param array<array-key, mixed> $attributes */
    private static function directionField(array $attributes): BackedEnum|string
    {
        $value = $attributes['direction'];

        return is_string($value) || ($value instanceof BackedEnum && is_string($value->value))
            ? $value
            : throw InvalidTradeException::invalidField('direction', "'buy', 'sell' or a string-backed enum", $value);
    }

    /** @param array<array-key, mixed> $attributes */
    private static function time(array $attributes, string $field): DateTimeInterface|string
    {
        $value = $attributes[$field];

        return is_string($value) || $value instanceof DateTimeInterface
            ? $value
            : throw InvalidTradeException::invalidField($field, 'a date string or DateTimeInterface', $value);
    }

    /**
     * An amount at the scale that keeps it exact. At the default scale 10 a 14-decimal size
     * lost its last digits, and a tiny position's value at entry truncated to 0.
     */
    private static function amount(string|int|float|NumericValueAsString $value): NumericValueAsString
    {
        return NumericValueAsString::of($value, scale: self::scaleOf($value));
    }

    private static function scaleOf(string|int|float|NumericValueAsString $value): int
    {
        if ($value instanceof NumericValueAsString) {
            return max(self::MIN_AMOUNT_SCALE, $value->getScale());
        }

        // A float casts to exponent notation ('1.0E-15'): its decimals are the fraction's
        // digits minus the exponent. Anything non-numeric is left to NumericValueAsString to refuse.
        if (preg_match('/^\s*[+-]?\d*(?:\.(\d*))?(?:[eE]([+-]?\d+))?\s*$/', (string) $value, $parts) !== 1) {
            return self::MIN_AMOUNT_SCALE;
        }

        $decimals = strlen($parts[1] ?? '') - (int) ($parts[2] ?? 0);

        return min(max(self::MIN_AMOUNT_SCALE, $decimals), self::MAX_AMOUNT_SCALE);
    }

    private static function parseDirection(BackedEnum|string $direction): Direction
    {
        if ($direction instanceof Direction) {
            return $direction;
        }

        $value = $direction instanceof BackedEnum ? (string) $direction->value : $direction;

        return Direction::tryFrom($value)
            ?? throw InvalidTradeException::invalidDirection($value);
    }

    private static function parseTime(DateTimeInterface|string $time): Carbon
    {
        if ($time instanceof Carbon) {
            return $time;
        }

        return $time instanceof DateTimeInterface ? Carbon::instance($time) : Carbon::parse($time);
    }

    private static function parseNullableTime(DateTimeInterface|string|null $time): ?Carbon
    {
        return $time === null ? null : self::parseTime($time);
    }

    public function pair(): string
    {
        return "{$this->baseCurrency}/{$this->quoteCurrency}";
    }

    public function isRealized(): bool
    {
        return $this->closeTime !== null;
    }

    public function isOpen(): bool
    {
        return ! $this->isRealized();
    }

    /**
     * The profit or loss, exact: at the trade's amount scale, widened to every decimal the
     * product needs, so a run at any scale truncates it only once, into its own result.
     */
    public function profitAndLoss(bool $subtractCommissions = false): NumericValueAsString
    {
        $scale = $this->amountScale();

        $move = $this->direction->isBuy()
            ? bcsub($this->closePrice->toRawString(), $this->openPrice->toRawString(), $scale)
            : bcsub($this->openPrice->toRawString(), $this->closePrice->toRawString(), $scale);

        // A difference of two amounts times a third carries at most twice their scale.
        $pnl = bcmul($move, $this->size->toRawString(), 2 * $scale);

        if ($subtractCommissions && $this->commission !== null) {
            $pnl = bcsub($pnl, $this->commission->toRawString(), 2 * $scale);
        }

        return new NumericValueAsString($pnl, scale: max($scale, self::decimalsOf($pnl)));
    }

    /**
     * The return on the value at entry (open price × size): the exact P&L divided by the exact
     * entry value at `$scale` — at least the trade's amount scale. Without a value at entry the
     * return is undefined and reads 0, like every other undefined ratio; the constructor refuses
     * the zero size or open price that would cause it.
     */
    public function roi(bool $subtractCommissions = false, bool $asPercentage = true, ?int $scale = null): NumericValueAsString
    {
        $amountScale = $this->amountScale();
        $scale = max($scale ?? 0, $amountScale);
        $entryValue = bcmul($this->openPrice->toRawString(), $this->size->toRawString(), 2 * $amountScale);

        $roi = bccomp($entryValue, '0', 2 * $amountScale) === 0
            ? new NumericValueAsString(scale: $scale)
            : new NumericValueAsString(
                value: bcdiv($this->profitAndLoss($subtractCommissions)->toRawString(), $entryValue, $scale),
                scale: $scale,
            );

        if (! $asPercentage) {
            return $roi;
        }

        return $roi->multiply(value: 100)
            ->round(2);
    }

    /** The widest scale among the trade's amounts — the scale their sums and differences are exact at. */
    private function amountScale(): int
    {
        return max(
            $this->openPrice->getScale(),
            $this->closePrice->getScale(),
            $this->size->getScale(),
            $this->commission?->getScale() ?? 0,
        );
    }

    /**
     * The decimal places a numeric string needs, trailing zeros dropped.
     *
     * @param  numeric-string  $value
     */
    private static function decimalsOf(string $value): int
    {
        $point = strpos($value, '.');

        return $point === false ? 0 : strlen(rtrim(substr($value, $point + 1), '0'));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'base_currency' => $this->baseCurrency,
            'quote_currency' => $this->quoteCurrency,
            'open_price' => $this->openPrice->toString(),
            'close_price' => $this->closePrice->toString(),
            'size' => $this->size->toString(),
            'direction' => $this->direction->value,
            'open_time' => $this->openTime->toDateTimeString(),
            'close_time' => $this->closeTime?->toDateTimeString(),
            'commission' => $this->commission?->toString(),
            'is_realized' => $this->isRealized(),
            'is_open' => $this->isOpen(),
            'pnl' => [
                'gross' => $this->profitAndLoss()->toString(),
                'net' => $this->profitAndLoss(subtractCommissions: true)->toString(),
            ],
            'roi' => [
                'gross' => $this->roi()->toString(),
                'net' => $this->roi(subtractCommissions: true)->toString(),
            ],
        ];
    }
}
