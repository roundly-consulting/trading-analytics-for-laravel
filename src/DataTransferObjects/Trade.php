<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\DataTransferObjects;

use Illuminate\Support\Carbon;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;

final class Trade
{
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
