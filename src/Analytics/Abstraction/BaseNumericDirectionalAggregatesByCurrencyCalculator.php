<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Analytics\Abstraction;

use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByDirections;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface;

abstract class BaseNumericDirectionalAggregatesByCurrencyCalculator implements AnalyticsInterface
{
    public static function calculatePerTrade(Analytics $analytics, Trade $trade): void
    {
        if (! static::shouldCalculatePerTrade($analytics, $trade)) {
            return;
        }

        static::calculateGlobalAnalyticsPerTrade($analytics, $trade);
        static::calculatePerPairAnalyticsPerTrade($analytics, $trade);
        static::calculatePerBaseCurrencyAnalyticsPerTrade($analytics, $trade);
        static::calculatePerQuoteCurrencyAnalyticsPerTrade($analytics, $trade);
    }

    public static function calculateAfterTrades(Analytics $analytics): void
    {
        static::calculateGlobalAverage($analytics);
        static::calculatePerPairAverage($analytics);
        static::calculatePerBaseCurrencyAverage($analytics);
        static::calculatePerQuoteCurrencyAverage($analytics);
        static::after($analytics);
    }

    protected static function after(Analytics $analytics): void
    {
        //
    }

    protected static function shouldCalculatePerTrade(Analytics $analytics, Trade $trade): bool
    {
        return true;
    }

    protected static function calculateGlobalAnalyticsPerTrade(Analytics $analytics, Trade $trade): void
    {
        // Total
        static::dto($analytics)->global->total->total->add(static::value($trade));

        // Highest
        if (static::dto($analytics)->global->total->highest->isLessThan(static::value($trade)) || static::dto($analytics)->global->total->highest->isZero()) {
            static::dto($analytics)->global->total->highest->set(static::value($trade));
            static::dto($analytics)->global->total->highestPair = $trade->pair();
        }

        // Lowest
        if (static::dto($analytics)->global->total->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->global->total->lowest->isZero()) {
            static::dto($analytics)->global->total->lowest->set(static::value($trade));
            static::dto($analytics)->global->total->lowestPair = $trade->pair();
        }

        if ($trade->direction->isBuy()) {
            // Total Buy
            static::dto($analytics)->global->buy->total->add(static::value($trade));

            // Highest Buy
            if (static::dto($analytics)->global->buy->highest->isLessThan(static::value($trade)) || static::dto($analytics)->global->buy->highest->isZero()) {
                static::dto($analytics)->global->buy->highest->set(static::value($trade));
                static::dto($analytics)->global->buy->highestPair = $trade->pair();
            }

            // Lowest Buy
            if (static::dto($analytics)->global->buy->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->global->buy->lowest->isZero()) {
                static::dto($analytics)->global->buy->lowest->set(static::value($trade));
                static::dto($analytics)->global->buy->lowestPair = $trade->pair();
            }
        } else {
            // Total Sell
            static::dto($analytics)->global->sell->total->add(static::value($trade));

            // Highest Sell
            if (static::dto($analytics)->global->sell->highest->isLessThan(static::value($trade)) || static::dto($analytics)->global->sell->highest->isZero()) {
                static::dto($analytics)->global->sell->highest->set(static::value($trade));
                static::dto($analytics)->global->sell->highestPair = $trade->pair();
            }

            // Lowest Sell
            if (static::dto($analytics)->global->sell->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->global->sell->lowest->isZero()) {
                static::dto($analytics)->global->sell->lowest->set(static::value($trade));
                static::dto($analytics)->global->sell->lowestPair = $trade->pair();
            }
        }
    }

    protected static function calculatePerPairAnalyticsPerTrade(Analytics $analytics, Trade $trade): void
    {
        // Total
        static::dto($analytics)->forPair($trade->pair())->total->total->add(static::value($trade));

        // Highest
        if (static::dto($analytics)->forPair($trade->pair())->total->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forPair($trade->pair())->total->highest->isZero()) {
            static::dto($analytics)->forPair($trade->pair())->total->highest->set(static::value($trade));
            static::dto($analytics)->forPair($trade->pair())->total->highestPair = $trade->pair();

        }

        // Lowest
        if (static::dto($analytics)->forPair($trade->pair())->total->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forPair($trade->pair())->total->lowest->isZero()) {
            static::dto($analytics)->forPair($trade->pair())->total->lowest->set(static::value($trade));
            static::dto($analytics)->forPair($trade->pair())->total->lowestPair = $trade->pair();
        }

        if ($trade->direction->isBuy()) {
            // Total Buy
            static::dto($analytics)->forPair($trade->pair())->buy->total->add(static::value($trade));

            // Highest Buy
            if (static::dto($analytics)->forPair($trade->pair())->buy->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forPair($trade->pair())->buy->highest->isZero()) {
                static::dto($analytics)->forPair($trade->pair())->buy->highest->set(static::value($trade));
                static::dto($analytics)->forPair($trade->pair())->buy->highestPair = $trade->pair();
            }

            // Lowest Buy
            if (static::dto($analytics)->forPair($trade->pair())->buy->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forPair($trade->pair())->buy->lowest->isZero()) {
                static::dto($analytics)->forPair($trade->pair())->buy->lowest->set(static::value($trade));
                static::dto($analytics)->forPair($trade->pair())->buy->lowestPair = $trade->pair();
            }
        } else {
            // Total Sell
            static::dto($analytics)->forPair($trade->pair())->sell->total->add(static::value($trade));

            // Highest Sell
            if (static::dto($analytics)->forPair($trade->pair())->sell->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forPair($trade->pair())->sell->highest->isZero()) {
                static::dto($analytics)->forPair($trade->pair())->sell->highest->set(static::value($trade));
                static::dto($analytics)->forPair($trade->pair())->sell->highestPair = $trade->pair();
            }

            // Lowest Sell
            if (static::dto($analytics)->forPair($trade->pair())->sell->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forPair($trade->pair())->sell->lowest->isZero()) {
                static::dto($analytics)->forPair($trade->pair())->sell->lowest->set(static::value($trade));
                static::dto($analytics)->forPair($trade->pair())->sell->lowestPair = $trade->pair();
            }
        }
    }

    protected static function calculatePerBaseCurrencyAnalyticsPerTrade(Analytics $analytics, Trade $trade): void
    {
        // Total
        static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->total->add(static::value($trade));

        // Highest
        if (static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->highest->isZero()) {
            static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->highest->set(static::value($trade));
            static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->highestPair = $trade->pair();
        }

        // Lowest
        if (static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->lowest->isZero()) {
            static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->lowest->set(static::value($trade));
            static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->total->lowestPair = $trade->pair();
        }

        if ($trade->direction->isBuy()) {
            // Total Buy
            static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->total->add(static::value($trade));

            // Highest Buy
            if (static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->highest->isZero()) {
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->highest->set(static::value($trade));
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->highestPair = $trade->pair();
            }

            // Lowest Buy
            if (static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->lowest->isZero()) {
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->lowest->set(static::value($trade));
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->buy->lowestPair = $trade->pair();
            }
        } else {
            // Total Sell
            static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->total->add(static::value($trade));

            // Highest Sell
            if (static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->highest->isZero()) {
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->highest->set(static::value($trade));
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->highestPair = $trade->pair();
            }

            // Lowest Sell
            if (static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->lowest->isZero()) {
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->lowest->set(static::value($trade));
                static::dto($analytics)->forBaseCurrency($trade->baseCurrency)->sell->lowestPair = $trade->pair();
            }
        }
    }

    protected static function calculatePerQuoteCurrencyAnalyticsPerTrade(Analytics $analytics, Trade $trade): void
    {
        // Total
        static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->total->add(static::value($trade));

        // Highest
        if (static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->highest->isZero()) {
            static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->highest->set(static::value($trade));
            static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->highestPair = $trade->pair();
        }

        // Lowest
        if (static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->lowest->isZero()) {
            static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->lowest->set(static::value($trade));
            static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->total->lowestPair = $trade->pair();
        }

        if ($trade->direction->isBuy()) {
            // Total Buy
            static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->total->add(static::value($trade));

            // Highest Buy
            if (static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->highest->isZero()) {
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->highest->set(static::value($trade));
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->highestPair = $trade->pair();
            }

            // Lowest Buy
            if (static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->lowest->isZero()) {
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->lowest->set(static::value($trade));
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->buy->lowestPair = $trade->pair();
            }
        } else {
            // Total Sell
            static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->total->add(static::value($trade));

            // Highest Sell
            if (static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->highest->isLessThan(static::value($trade)) || static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->highest->isZero()) {
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->highest->set(static::value($trade));
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->highestPair = $trade->pair();
            }

            // Lowest Sell
            if (static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->lowest->isGreaterThan(static::value($trade)) || static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->lowest->isZero()) {
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->lowest->set(static::value($trade));
                static::dto($analytics)->forQuoteCurrency($trade->quoteCurrency)->sell->lowestPair = $trade->pair();
            }
        }
    }

    public static function calculateGlobalAverage(Analytics $analytics): void
    {
        // Total
        if ($analytics->counts->global->total->isNonZero()) {
            static::dto($analytics)->global->total->average = static::dto($analytics)->global->total->total->divide(
                value: $analytics->counts->global->total,
                immutable: true,
            );
        }

        // Buy
        if ($analytics->counts->global->buy->isNonZero()) {
            static::dto($analytics)->global->buy->average = static::dto($analytics)->global->buy->total->divide(
                value: $analytics->counts->global->buy,
                immutable: true,
            );
        }

        // Sell
        if ($analytics->counts->global->sell->isNonZero()) {
            static::dto($analytics)->global->sell->average = static::dto($analytics)->global->sell->total->divide(
                value: $analytics->counts->global->sell,
                immutable: true,
            );
        }
    }

    public static function calculatePerPairAverage(Analytics $analytics): void
    {
        /** @var NumericDirectionalAggregates $pairs */
        foreach (static::dto($analytics)->perPair as $pair => $pairs) {
            static::calculateAveragesFromTotals(
                numericByDirections: $analytics->counts->forPair($pair),
                numericDirectionalAggregates: $pairs,
            );
        }
    }

    public static function calculatePerBaseCurrencyAverage(Analytics $analytics): void
    {
        /** @var NumericDirectionalAggregates $baseCurrencys */
        foreach (static::dto($analytics)->perBaseCurrency as $pair => $baseCurrencys) {
            static::calculateAveragesFromTotals(
                numericByDirections: $analytics->counts->forBaseCurrency($pair),
                numericDirectionalAggregates: $baseCurrencys,
            );
        }
    }

    public static function calculatePerQuoteCurrencyAverage(Analytics $analytics): void
    {
        /** @var NumericDirectionalAggregates $quoteCurrencys */
        foreach (static::dto($analytics)->perQuoteCurrency as $pair => $quoteCurrencys) {
            static::calculateAveragesFromTotals(
                numericByDirections: $analytics->counts->forQuoteCurrency($pair),
                numericDirectionalAggregates: $quoteCurrencys,
            );
        }
    }

    protected static function dto(Analytics $analytics): NumericDirectionalAggregatesByCurrency
    {
        return $analytics->volume;
    }

    protected static function value(Trade $trade): NumericValueAsString
    {
        return $trade->size;
    }

    protected static function calculateAveragesFromTotals(
        NumericByDirections $numericByDirections,
        NumericDirectionalAggregates $numericDirectionalAggregates
    ): void {
        // Total
        if ($numericByDirections->total->isNonZero()) {
            $numericDirectionalAggregates->total->average = $numericDirectionalAggregates->total->total->divide(
                value: $numericByDirections->total,
                immutable: true,
            );
        }

        // Buy
        if ($numericByDirections->buy->isNonZero()) {
            $numericDirectionalAggregates->buy->average = $numericDirectionalAggregates->buy->total->divide(
                value: $numericByDirections->buy,
                immutable: true,
            );
        }

        // Sell
        if ($numericByDirections->sell->isNonZero()) {
            $numericDirectionalAggregates->sell->average = $numericDirectionalAggregates->sell->total->divide(
                value: $numericByDirections->sell,
                immutable: true,
            );
        }
    }
}
