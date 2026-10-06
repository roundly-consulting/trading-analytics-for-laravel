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
            openPrice: NumericValueAsString::of($openPrice),
            closePrice: NumericValueAsString::of($closePrice),
            size: NumericValueAsString::of($size),
            direction: self::parseDirection($direction),
            openTime: self::parseTime($openTime),
            commission: $commission === null ? null : NumericValueAsString::of($commission),
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

    public function profitAndLoss(bool $subtractCommissions = false): NumericValueAsString
    {
        if ($this->direction->isBuy()) {
            $pnl = $this->closePrice->subtract(
                value: $this->openPrice,
                immutable: true,
            )->multiply(
                value: $this->size,
                immutable: true,
            );
        } else {
            $pnl = $this->openPrice->subtract(
                value: $this->closePrice,
                immutable: true,
            )->multiply(
                value: $this->size,
                immutable: true,
            );
        }

        if ($subtractCommissions && $this->commission) {
            $pnl = $pnl->subtract(
                value: $this->commission,
                immutable: true,
            );
        }

        return $pnl;
    }

    public function roi(bool $subtractCommissions = false, bool $asPercentage = true): NumericValueAsString
    {
        $pnl = $this->profitAndLoss($subtractCommissions);

        $roi = $pnl->divide(
            value: $this->openPrice->multiply(
                value: $this->size,
                immutable: true,
            ),
            immutable: true,
        );

        if (! $asPercentage) {
            return $roi;
        }

        return $roi->multiply(value: 100, immutable: true)
            ->round(2);
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
