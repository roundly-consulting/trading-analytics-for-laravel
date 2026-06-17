<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use JsonSerializable;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Traits\SerializesToJson;

/** @implements Arrayable<string, mixed> */
final class Trade implements Arrayable, Jsonable, JsonSerializable
{
    use SerializesToJson;

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
    }

    /**
     * Build a trade from scalar values, wrapping the numeric fields in
     * {@see NumericValueAsString} for the caller so they never type
     * `new NumericValueAsString(...)` by hand.
     */
    public static function make(
        string $baseCurrency,
        string $quoteCurrency,
        string|int|float|NumericValueAsString $openPrice,
        string|int|float|NumericValueAsString $closePrice,
        string|int|float|NumericValueAsString $size,
        Direction|string $direction,
        Carbon|string $openTime,
        string|int|float|NumericValueAsString|null $commission = null,
        Carbon|string|null $closeTime = null,
    ): self {
        return new self(
            baseCurrency: $baseCurrency,
            quoteCurrency: $quoteCurrency,
            openPrice: NumericValueAsString::of($openPrice),
            closePrice: NumericValueAsString::of($closePrice),
            size: NumericValueAsString::of($size),
            direction: self::parseDirection($direction),
            openTime: $openTime instanceof Carbon ? $openTime : Carbon::parse($openTime),
            commission: $commission === null ? null : NumericValueAsString::of($commission),
            closeTime: self::parseNullableTime($closeTime),
        );
    }

    /**
     * Ingestion boundary: build a trade from a row of scalars (e.g. a database
     * record or API payload). Internals stay strictly DTO-typed; this method is
     * the documented array entry point.
     *
     * @param array{
     *     base_currency: string,
     *     quote_currency: string,
     *     open_price: string|int|float,
     *     close_price: string|int|float,
     *     size: string|int|float,
     *     direction: string|Direction,
     *     open_time: string|Carbon,
     *     commission?: string|int|float|null,
     *     close_time?: string|Carbon|null
     * } $attributes
     */
    public static function fromArray(array $attributes): self
    {
        foreach (['base_currency', 'quote_currency', 'open_price', 'close_price', 'size', 'direction', 'open_time'] as $required) {
            if (! array_key_exists($required, $attributes)) {
                throw InvalidTradeException::missingField($required);
            }
        }

        return self::make(
            baseCurrency: $attributes['base_currency'],
            quoteCurrency: $attributes['quote_currency'],
            openPrice: $attributes['open_price'],
            closePrice: $attributes['close_price'],
            size: $attributes['size'],
            direction: $attributes['direction'],
            openTime: $attributes['open_time'],
            commission: $attributes['commission'] ?? null,
            closeTime: $attributes['close_time'] ?? null,
        );
    }

    /**
     * Lazily map an iterable of rows (arrays or trades) into trades, so
     * `Analytics::make(Trade::collect($query->lazy()))` is a one-liner.
     *
     * @param  iterable<int, array{base_currency: string, quote_currency: string, open_price: string|int|float, close_price: string|int|float, size: string|int|float, direction: string|Direction, open_time: string|Carbon, commission?: string|int|float|null, close_time?: string|Carbon|null}|Trade>  $rows
     * @return LazyCollection<int, Trade>
     */
    public static function collect(iterable $rows): LazyCollection
    {
        return LazyCollection::make(static function () use ($rows): iterable {
            foreach ($rows as $row) {
                yield $row instanceof self ? $row : self::fromArray($row);
            }
        });
    }

    private static function parseDirection(Direction|string $direction): Direction
    {
        if ($direction instanceof Direction) {
            return $direction;
        }

        return Direction::tryFrom($direction)
            ?? throw InvalidTradeException::invalidDirection($direction);
    }

    private static function parseNullableTime(Carbon|string|null $time): ?Carbon
    {
        if ($time === null) {
            return null;
        }

        return $time instanceof Carbon ? $time : Carbon::parse($time);
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
