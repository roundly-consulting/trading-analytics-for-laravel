# Trading Analytics for Laravel

Calculate trading performance analytics — P&L, returns, streaks, profit factor — for Laravel.

This is a pure calculation library: feed it a `LazyCollection` of `Trade` objects and it computes
a full set of trading performance metrics (counts, wins, volume, value, commissions, realized and
unrealized profit & loss, profit factor, cumulative return, frequency, duration and streaks),
broken down globally and per trading pair / base currency / quote currency. All amounts are computed
with arbitrary-precision `bcmath` arithmetic and returned as immutable result objects.

## Requirements

- PHP `^8.3`
- Laravel `^12.0` or `^13.0`
- The `bcmath` PHP extension

## Installation

Install the package via Composer:

```bash
composer require roundly-consulting/trading-analytics-for-laravel
```

The service provider is auto-discovered. The package ships no config file, migrations, views, or
commands — it is a calculation library you use directly in your own code.

## Usage

```php
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

// Create LazyCollection of trades using generator or lazy select from database
$trades = LazyCollection::make(function () {
    yield new Trade(...);
    yield new Trade(...);
    yield new Trade(...);
});

// Create analytics object with trades
$analytics = new Analytics($trades);

// Change default scale of calculations and precision
$analytics->scale(5);
$analytics->getScale(); // 5

// Check if analytics has been calculated
$analytics->hasBeenCalculated(); // false

// Calculate analytics
$analytics->calculate();

// Check if analytics has been calculated
$analytics->hasBeenCalculated(); // true

// Access DataTransferObjects from analytics to get calculated data
$analytics->counts // returns Counts DataTransferObject
$analytics->wins // returns Wins DataTransferObject
$analytics->volume // returns TradingVolume DataTransferObject
$analytics->value // returns TradingValue DataTransferObject
$analytics->unrealizedProfitAndLoss // returns ProfitAndLoss DataTransferObject
$analytics->realizedProfitAndLoss // returns ProfitAndLoss DataTransferObject
$analytics->profitFactor // returns ProfitFactor DataTransferObject
$analytics->comission // returns TradingCommissions DataTransferObject
$analytics->cumulativeReturn // returns CumulativeReturn DataTransferObject
$analytics->frequency // returns TradingFrequency DataTransferObject
$analytics->duration // returns TradesDuration DataTransferObject
$analytics->streaks // returns Streaks DataTransferObject

// Example of accessing data from DataTransferObject
$analytics->wins->global->total // returns total number of wins as NumericValueAsString object (can be casted to string)
$analytics->wins->global->buy // returns number of buy wins
$analytics->wins->global->sell // returns number of sell wins

$analytics->wins->forPair('BTC/USDT')->total // returns total number of wins for pair BTC/USDT
$analytics->wins->forPair('BTC/USDT')->buy // returns number of buy wins for pair BTC/USDT
$analytics->wins->forPair('BTC/USDT')->sell // returns number of sell wins for pair BTC/USDT

$analytics->wins->forBaseCurrency('BTC')->total // returns total number of wins for base currency BTC
$analytics->wins->forBaseCurrency('BTC')->buy // returns number of buy wins for base currency BTC
$analytics->wins->forBaseCurrency('BTC')->sell // returns number of sell wins for base currency BTC

$analytics->wins->forQuoteCurrency('USDT')->total // returns total number of wins for quote currency USDT
$analytics->wins->forQuoteCurrency('USDT')->buy // returns number of buy wins for quote currency USDT
$analytics->wins->forQuoteCurrency('USDT')->sell // returns number of sell wins for quote currency USDT

// You can cast whole analytics object to array
$analytics->toArray(); // returns array with all calculated data

// example:

[
    'counts' => [
        'global' => [
            'total' => '5',
            'buy' => '3',
            'sell' => '2',
        ],
        'per_base_currency' => [
            'BTC' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
            'ETH' => [
                'total' => '3',
                'buy' => '2',
                'sell' => '1',
            ],
            'XRP' => [
                'total' => '1',
                'buy' => '0',
                'sell' => '1',
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'total' => '5',
                'buy' => '3',
                'sell' => '2',
            ],
        ],
        'per_pair' => [
            'BTC/USD' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
            'ETH/USD' => [
                'total' => '3',
                'buy' => '2',
                'sell' => '1',
            ],
            'XRP/USD' => [
                'total' => '1',
                'buy' => '0',
                'sell' => '1',
            ],
        ],
    ],
    'wins' => [
        'global' => [
            'total' => '2',
            'buy' => '2',
            'sell' => '0',
        ],
        'per_base_currency' => [
            'BTC' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
            'ETH' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'total' => '2',
                'buy' => '2',
                'sell' => '0',
            ],
        ],
        'per_pair' => [
            'BTC/USD' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
            'ETH/USD' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
        ],
        'win_ratio' => [
            'global' => [
                'total' => '0.40',
                'buy' => '0.66',
                'sell' => '0.00',
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => '1.00',
                    'buy' => '1.00',
                    'sell' => '0.00',
                ],
                'ETH' => [
                    'total' => '0.33',
                    'buy' => '0.50',
                    'sell' => '0.00',
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => '0.40',
                    'buy' => '0.66',
                    'sell' => '0.00',
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => '1.00',
                    'buy' => '1.00',
                    'sell' => '0.00',
                ],
                'ETH/USD' => [
                    'total' => '0.33',
                    'buy' => '0.50',
                    'sell' => '0.00',
                ],
            ],
        ],
    ],
    'volume' => [
        'global' => [
            'total' => [
                'total' => '1023.6000000000',
                'average' => '204.7200000000',
                'highest' => [
                    'value' => '1000.0000000000',
                    'pair' => 'XRP/USD',
                ],
                'lowest' => [
                    'value' => '0.1000000000',
                    'pair' => 'BTC/USD',
                ],
            ],
            'buy' => [
                'total' => '22.1000000000',
                'average' => '7.3666666666',
                'highest' => [
                    'value' => '12.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '0.1000000000',
                    'pair' => 'BTC/USD',
                ],
            ],
            'sell' => [
                'total' => '1001.5000000000',
                'average' => '500.7500000000',
                'highest' => [
                    'value' => '1000.0000000000',
                    'pair' => 'XRP/USD',
                ],
                'lowest' => [
                    'value' => '1.5000000000',
                    'pair' => 'ETH/USD',
                ],
            ],
        ],
        'per_pair' => [
            'BTC/USD' => [
                'total' => [
                    'total' => '0.1000000000',
                    'average' => '0.1000000000',
                    'highest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.1000000000',
                    'average' => '0.1000000000',
                    'highest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH/USD' => [
                'total' => [
                    'total' => '23.5000000000',
                    'average' => '7.8333333333',
                    'highest' => [
                        'value' => '12.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '22.0000000000',
                    'average' => '11.0000000000',
                    'highest' => [
                        'value' => '12.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '1.5000000000',
                    'average' => '1.5000000000',
                    'highest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP/USD' => [
                'total' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_base_currency' => [
            'BTC' => [
                'total' => [
                    'total' => '0.1000000000',
                    'average' => '0.1000000000',
                    'highest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.1000000000',
                    'average' => '0.1000000000',
                    'highest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH' => [
                'total' => [
                    'total' => '23.5000000000',
                    'average' => '7.8333333333',
                    'highest' => [
                        'value' => '12.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '22.0000000000',
                    'average' => '11.0000000000',
                    'highest' => [
                        'value' => '12.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '1.5000000000',
                    'average' => '1.5000000000',
                    'highest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP' => [
                'total' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'total' => [
                    'total' => '1023.6000000000',
                    'average' => '204.7200000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '22.1000000000',
                    'average' => '7.3666666666',
                    'highest' => [
                        'value' => '12.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '1001.5000000000',
                    'average' => '500.7500000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1.5000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
        ],
    ],
    'value' => [
        'global' => [
            'total' => [
                'total' => '82000.0000000000',
                'average' => '16400.0000000000',
                'highest' => [
                    'value' => '42000.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '1000.0000000000',
                    'pair' => 'XRP/USD',
                ],
            ],
            'buy' => [
                'total' => '76500.0000000000',
                'average' => '25500.0000000000',
                'highest' => [
                    'value' => '42000.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '4500.0000000000',
                    'pair' => 'BTC/USD',
                ],
            ],
            'sell' => [
                'total' => '5500.0000000000',
                'average' => '2750.0000000000',
                'highest' => [
                    'value' => '4500.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '1000.0000000000',
                    'pair' => 'XRP/USD',
                ],
            ],
        ],
        'per_pair' => [
            'BTC/USD' => [
                'total' => [
                    'total' => '4500.0000000000',
                    'average' => '4500.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '4500.0000000000',
                    'average' => '4500.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH/USD' => [
                'total' => [
                    'total' => '76500.0000000000',
                    'average' => '25500.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '72000.0000000000',
                    'average' => '36000.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '30000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '4500.0000000000',
                    'average' => '4500.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP/USD' => [
                'total' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_base_currency' => [
            'BTC' => [
                'total' => [
                    'total' => '4500.0000000000',
                    'average' => '4500.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '4500.0000000000',
                    'average' => '4500.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH' => [
                'total' => [
                    'total' => '76500.0000000000',
                    'average' => '25500.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '72000.0000000000',
                    'average' => '36000.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '30000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '4500.0000000000',
                    'average' => '4500.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP' => [
                'total' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '1000.0000000000',
                    'average' => '1000.0000000000',
                    'highest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'total' => [
                    'total' => '82000.0000000000',
                    'average' => '16400.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '76500.0000000000',
                    'average' => '25500.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '5500.0000000000',
                    'average' => '2750.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
    ],
    'comission' => [
        'global' => [
            'total' => [
                'total' => '2132.0000000000',
                'average' => '426.4000000000',
                'highest' => [
                    'value' => '1123.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '2.0000000000',
                    'pair' => 'XRP/USD',
                ],
            ],
            'buy' => [
                'total' => '2120.0000000000',
                'average' => '706.6666666666',
                'highest' => [
                    'value' => '1123.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '15.0000000000',
                    'pair' => 'BTC/USD',
                ],
            ],
            'sell' => [
                'total' => '12.0000000000',
                'average' => '6.0000000000',
                'highest' => [
                    'value' => '10.0000000000',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '2.0000000000',
                    'pair' => 'XRP/USD',
                ],
            ],
        ],
        'per_pair' => [
            'BTC/USD' => [
                'total' => [
                    'total' => '15.0000000000',
                    'average' => '15.0000000000',
                    'highest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '15.0000000000',
                    'average' => '15.0000000000',
                    'highest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH/USD' => [
                'total' => [
                    'total' => '2115.0000000000',
                    'average' => '705.0000000000',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '2105.0000000000',
                    'average' => '1052.5000000000',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '982.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '10.0000000000',
                    'average' => '10.0000000000',
                    'highest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP/USD' => [
                'total' => [
                    'total' => '2.0000000000',
                    'average' => '2.0000000000',
                    'highest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '2.0000000000',
                    'average' => '2.0000000000',
                    'highest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_base_currency' => [
            'BTC' => [
                'total' => [
                    'total' => '15.0000000000',
                    'average' => '15.0000000000',
                    'highest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '15.0000000000',
                    'average' => '15.0000000000',
                    'highest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH' => [
                'total' => [
                    'total' => '2115.0000000000',
                    'average' => '705.0000000000',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '2105.0000000000',
                    'average' => '1052.5000000000',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '982.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '10.0000000000',
                    'average' => '10.0000000000',
                    'highest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP' => [
                'total' => [
                    'total' => '2.0000000000',
                    'average' => '2.0000000000',
                    'highest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.0000000000',
                    'average' => '0.0000000000',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '2.0000000000',
                    'average' => '2.0000000000',
                    'highest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'total' => [
                    'total' => '2132.0000000000',
                    'average' => '426.4000000000',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '2120.0000000000',
                    'average' => '706.6666666666',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '12.0000000000',
                    'average' => '6.0000000000',
                    'highest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
    ],
    'profit_and_loss' => [
        'unrealized' => [
            'gross' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '0.0000000000',
                            'average' => '0.0000000000',
                            'highest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                            'lowest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                        ],
                        'buy' => [
                            'total' => '0.0000000000',
                            'average' => '0.0000000000',
                            'highest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                            'lowest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                        ],
                        'sell' => [
                            'total' => '0.0000000000',
                            'average' => '0.0000000000',
                            'highest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                            'lowest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                        ],
                    ],
                    'per_pair' => [],
                    'per_base_currency' => [],
                    'per_quote_currency' => [],
                ],
                'profits' => [
                    'total' => '2050.0000000000',
                    'per_pair' => [
                        'BTC/USD' => '50.0000000000',
                        'ETH/USD' => '2000.0000000000',
                    ],
                    'per_base_currency' => [
                        'BTC' => '50.0000000000',
                        'ETH' => '2000.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '2050.0000000000',
                    ],
                ],
                'losses' => [
                    'total' => '-1290.0000000000',
                    'per_pair' => [
                        'ETH/USD' => '-1140.0000000000',
                        'XRP/USD' => '-150.0000000000',
                    ],
                    'per_base_currency' => [
                        'ETH' => '-1140.0000000000',
                        'XRP' => '-150.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '-1290.0000000000',
                    ],
                ],
            ],
            'net' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '0.0000000000',
                            'average' => '0.0000000000',
                            'highest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                            'lowest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                        ],
                        'buy' => [
                            'total' => '0.0000000000',
                            'average' => '0.0000000000',
                            'highest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                            'lowest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                        ],
                        'sell' => [
                            'total' => '0.0000000000',
                            'average' => '0.0000000000',
                            'highest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                            'lowest' => [
                                'value' => '0.0000000000',
                                'pair' => '',
                            ],
                        ],
                    ],
                    'per_pair' => [],
                    'per_base_currency' => [],
                    'per_quote_currency' => [],
                ],
                'profits' => [
                    'total' => '1053.0000000000',
                    'per_pair' => [
                        'BTC/USD' => '35.0000000000',
                        'ETH/USD' => '1018.0000000000',
                    ],
                    'per_base_currency' => [
                        'BTC' => '35.0000000000',
                        'ETH' => '1018.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '1053.0000000000',
                    ],
                ],
                'losses' => [
                    'total' => '-2425.0000000000',
                    'per_pair' => [
                        'ETH/USD' => '-2273.0000000000',
                        'XRP/USD' => '-152.0000000000',
                    ],
                    'per_base_currency' => [
                        'ETH' => '-2273.0000000000',
                        'XRP' => '-152.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '-2425.0000000000',
                    ],
                ],
            ],
        ],
        'realized' => [
            'gross' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '760.0000000000',
                            'average' => '152.0000000000',
                            'highest' => [
                                'value' => '2000.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                            'lowest' => [
                                'value' => '-840.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '1210.0000000000',
                            'average' => '403.3333333333',
                            'highest' => [
                                'value' => '2000.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                            'lowest' => [
                                'value' => '-840.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '-450.0000000000',
                            'average' => '-225.0000000000',
                            'highest' => [
                                'value' => '-150.0000000000',
                                'pair' => 'XRP/USD',
                            ],
                            'lowest' => [
                                'value' => '-300.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                        ],
                    ],
                    'per_pair' => [
                        'BTC/USD' => [
                            'total' => [
                                'total' => '50.0000000000',
                                'average' => '50.0000000000',
                                'highest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '50.0000000000',
                                'average' => '50.0000000000',
                                'highest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                        ],
                        'ETH/USD' => [
                            'total' => [
                                'total' => '860.0000000000',
                                'average' => '286.6666666666',
                                'highest' => [
                                    'value' => '2000.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-840.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '1160.0000000000',
                                'average' => '580.0000000000',
                                'highest' => [
                                    'value' => '2000.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-840.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '-300.0000000000',
                                'average' => '-300.0000000000',
                                'highest' => [
                                    'value' => '-300.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-300.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                        ],
                        'XRP/USD' => [
                            'total' => [
                                'total' => '-150.0000000000',
                                'average' => '-150.0000000000',
                                'highest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                            'sell' => [
                                'total' => '-150.0000000000',
                                'average' => '-150.0000000000',
                                'highest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                        ],
                    ],
                    'per_base_currency' => [
                        'BTC' => [
                            'total' => [
                                'total' => '50.0000000000',
                                'average' => '50.0000000000',
                                'highest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '50.0000000000',
                                'average' => '50.0000000000',
                                'highest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '50.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                        ],
                        'ETH' => [
                            'total' => [
                                'total' => '860.0000000000',
                                'average' => '286.6666666666',
                                'highest' => [
                                    'value' => '2000.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-840.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '1160.0000000000',
                                'average' => '580.0000000000',
                                'highest' => [
                                    'value' => '2000.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-840.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '-300.0000000000',
                                'average' => '-300.0000000000',
                                'highest' => [
                                    'value' => '-300.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-300.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                        ],
                        'XRP' => [
                            'total' => [
                                'total' => '-150.0000000000',
                                'average' => '-150.0000000000',
                                'highest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                            'sell' => [
                                'total' => '-150.0000000000',
                                'average' => '-150.0000000000',
                                'highest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                        ],
                    ],
                    'per_quote_currency' => [
                        'USD' => [
                            'total' => [
                                'total' => '760.0000000000',
                                'average' => '152.0000000000',
                                'highest' => [
                                    'value' => '2000.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-840.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '1210.0000000000',
                                'average' => '403.3333333333',
                                'highest' => [
                                    'value' => '2000.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-840.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '-450.0000000000',
                                'average' => '-225.0000000000',
                                'highest' => [
                                    'value' => '-150.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-300.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                        ],
                    ],
                ],
                'profits' => [
                    'total' => '2050.0000000000',
                    'per_pair' => [
                        'BTC/USD' => '50.0000000000',
                        'ETH/USD' => '2000.0000000000',
                    ],
                    'per_base_currency' => [
                        'BTC' => '50.0000000000',
                        'ETH' => '2000.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '2050.0000000000',
                    ],
                ],
                'losses' => [
                    'total' => '-1290.0000000000',
                    'per_pair' => [
                        'ETH/USD' => '-1140.0000000000',
                        'XRP/USD' => '-150.0000000000',
                    ],
                    'per_base_currency' => [
                        'ETH' => '-1140.0000000000',
                        'XRP' => '-150.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '-1290.0000000000',
                    ],
                ],
            ],
            'net' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '-1372.0000000000',
                            'average' => '-274.4000000000',
                            'highest' => [
                                'value' => '1018.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                            'lowest' => [
                                'value' => '-1963.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '-910.0000000000',
                            'average' => '-303.3333333333',
                            'highest' => [
                                'value' => '1018.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                            'lowest' => [
                                'value' => '-1963.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '-462.0000000000',
                            'average' => '-231.0000000000',
                            'highest' => [
                                'value' => '-152.0000000000',
                                'pair' => 'XRP/USD',
                            ],
                            'lowest' => [
                                'value' => '-310.0000000000',
                                'pair' => 'ETH/USD',
                            ],
                        ],
                    ],
                    'per_pair' => [
                        'BTC/USD' => [
                            'total' => [
                                'total' => '35.0000000000',
                                'average' => '35.0000000000',
                                'highest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '35.0000000000',
                                'average' => '35.0000000000',
                                'highest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                        ],
                        'ETH/USD' => [
                            'total' => [
                                'total' => '-1255.0000000000',
                                'average' => '-418.3333333333',
                                'highest' => [
                                    'value' => '1018.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-1963.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '-945.0000000000',
                                'average' => '-472.5000000000',
                                'highest' => [
                                    'value' => '1018.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-1963.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '-310.0000000000',
                                'average' => '-310.0000000000',
                                'highest' => [
                                    'value' => '-310.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-310.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                        ],
                        'XRP/USD' => [
                            'total' => [
                                'total' => '-152.0000000000',
                                'average' => '-152.0000000000',
                                'highest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                            'sell' => [
                                'total' => '-152.0000000000',
                                'average' => '-152.0000000000',
                                'highest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                        ],
                    ],
                    'per_base_currency' => [
                        'BTC' => [
                            'total' => [
                                'total' => '35.0000000000',
                                'average' => '35.0000000000',
                                'highest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '35.0000000000',
                                'average' => '35.0000000000',
                                'highest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                                'lowest' => [
                                    'value' => '35.0000000000',
                                    'pair' => 'BTC/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                        ],
                        'ETH' => [
                            'total' => [
                                'total' => '-1255.0000000000',
                                'average' => '-418.3333333333',
                                'highest' => [
                                    'value' => '1018.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-1963.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '-945.0000000000',
                                'average' => '-472.5000000000',
                                'highest' => [
                                    'value' => '1018.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-1963.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '-310.0000000000',
                                'average' => '-310.0000000000',
                                'highest' => [
                                    'value' => '-310.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-310.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                        ],
                        'XRP' => [
                            'total' => [
                                'total' => '-152.0000000000',
                                'average' => '-152.0000000000',
                                'highest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '0.0000000000',
                                'average' => '0.0000000000',
                                'highest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                                'lowest' => [
                                    'value' => '0.0000000000',
                                    'pair' => '',
                                ],
                            ],
                            'sell' => [
                                'total' => '-152.0000000000',
                                'average' => '-152.0000000000',
                                'highest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                            ],
                        ],
                    ],
                    'per_quote_currency' => [
                        'USD' => [
                            'total' => [
                                'total' => '-1372.0000000000',
                                'average' => '-274.4000000000',
                                'highest' => [
                                    'value' => '1018.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-1963.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '-910.0000000000',
                                'average' => '-303.3333333333',
                                'highest' => [
                                    'value' => '1018.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                                'lowest' => [
                                    'value' => '-1963.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                            'sell' => [
                                'total' => '-462.0000000000',
                                'average' => '-231.0000000000',
                                'highest' => [
                                    'value' => '-152.0000000000',
                                    'pair' => 'XRP/USD',
                                ],
                                'lowest' => [
                                    'value' => '-310.0000000000',
                                    'pair' => 'ETH/USD',
                                ],
                            ],
                        ],
                    ],
                ],
                'profits' => [
                    'total' => '1053.0000000000',
                    'per_pair' => [
                        'BTC/USD' => '35.0000000000',
                        'ETH/USD' => '1018.0000000000',
                    ],
                    'per_base_currency' => [
                        'BTC' => '35.0000000000',
                        'ETH' => '1018.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '1053.0000000000',
                    ],
                ],
                'losses' => [
                    'total' => '-2425.0000000000',
                    'per_pair' => [
                        'ETH/USD' => '-2273.0000000000',
                        'XRP/USD' => '-152.0000000000',
                    ],
                    'per_base_currency' => [
                        'ETH' => '-2273.0000000000',
                        'XRP' => '-152.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '-2425.0000000000',
                    ],
                ],
            ],
        ],
    ],
    'profit_factor' => [
        'total' => '1.58',
        'per_pair' => [
            'BTC/USD' => '0.00',
            'ETH/USD' => '1.75',
        ],
        'per_base_currency' => [
            'BTC' => '0.00',
            'ETH' => '1.75',
        ],
        'per_quote_currency' => [
            'USD' => '1.58',
        ],
    ],
    'cumulative_return' => [
        'gross' => [
            'global' => [
                'total' => [
                    'total' => '-16.14',
                    'average' => '-3.46',
                    'highest' => [
                        'value' => '1.11',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '-16.14',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '5.69',
                    'average' => '1.86',
                    'highest' => [
                        'value' => '7.85',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1.11',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '-20.66',
                    'average' => '-10.93',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '-20.66',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '1.11',
                        'average' => '1.11',
                        'highest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1.11',
                        'average' => '1.11',
                        'highest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                ],
                'ETH/USD' => [
                    'total' => [
                        'total' => '-2.43',
                        'average' => '-0.81',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-6.66',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '4.53',
                        'average' => '2.24',
                        'highest' => [
                            'value' => '6.66',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4.53',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-6.66',
                        'average' => '-6.66',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-6.66',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP/USD' => [
                    'total' => [
                        'total' => '-15.00',
                        'average' => '-15.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.00',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                    'sell' => [
                        'total' => '-15.00',
                        'average' => '-15.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.00',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '1.11',
                        'average' => '1.11',
                        'highest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1.11',
                        'average' => '1.11',
                        'highest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                ],
                'ETH' => [
                    'total' => [
                        'total' => '-2.43',
                        'average' => '-0.81',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-6.66',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '4.53',
                        'average' => '2.24',
                        'highest' => [
                            'value' => '6.66',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4.53',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-6.66',
                        'average' => '-6.66',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-6.66',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP' => [
                    'total' => [
                        'total' => '-15.00',
                        'average' => '-15.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.00',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                    'sell' => [
                        'total' => '-15.00',
                        'average' => '-15.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.00',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '-16.14',
                        'average' => '-3.46',
                        'highest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '-16.14',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '5.69',
                        'average' => '1.86',
                        'highest' => [
                            'value' => '7.85',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '1.11',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-20.66',
                        'average' => '-10.93',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-20.66',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
        ],
        'net' => [
            'global' => [
                'total' => [
                    'total' => '-21.57',
                    'average' => '-4.74',
                    'highest' => [
                        'value' => '0.77',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '-21.57',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '-0.67',
                    'average' => '-0.22',
                    'highest' => [
                        'value' => '4.19',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '-0.67',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '-21.04',
                    'average' => '-11.14',
                    'highest' => [
                        'value' => '0.0000000000',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '-21.04',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '0.77',
                        'average' => '0.77',
                        'highest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.77',
                        'average' => '0.77',
                        'highest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                ],
                'ETH/USD' => [
                    'total' => [
                        'total' => '-8.22',
                        'average' => '-2.82',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-8.22',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '-1.43',
                        'average' => '-0.72',
                        'highest' => [
                            'value' => '3.39',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1.43',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-6.88',
                        'average' => '-6.88',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-6.88',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP/USD' => [
                    'total' => [
                        'total' => '-15.20',
                        'average' => '-15.20',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.20',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                    'sell' => [
                        'total' => '-15.20',
                        'average' => '-15.20',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.20',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '0.77',
                        'average' => '0.77',
                        'highest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.77',
                        'average' => '0.77',
                        'highest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                ],
                'ETH' => [
                    'total' => [
                        'total' => '-8.22',
                        'average' => '-2.82',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-8.22',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '-1.43',
                        'average' => '-0.72',
                        'highest' => [
                            'value' => '3.39',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1.43',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-6.88',
                        'average' => '-6.88',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-6.88',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP' => [
                    'total' => [
                        'total' => '-15.20',
                        'average' => '-15.20',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.20',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                    ],
                    'sell' => [
                        'total' => '-15.20',
                        'average' => '-15.20',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-15.20',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '-21.57',
                        'average' => '-4.74',
                        'highest' => [
                            'value' => '0.77',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '-21.57',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '-0.67',
                        'average' => '-0.22',
                        'highest' => [
                            'value' => '4.19',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-0.67',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-21.04',
                        'average' => '-11.14',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '-21.04',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'frequency' => [
        'total' => [
            'value' => '1.0',
            'scale' => 1,
            'prefix' => '',
            'suffix' => 'per month',
            'formatted' => '1.0 per month',
        ],
        'per_pair' => [
            'BTC/USD' => [
                'value' => '0.0',
                'scale' => 1,
                'prefix' => '',
                'suffix' => '',
                'formatted' => '0.0',
            ],
            'ETH/USD' => [
                'value' => '7.9',
                'scale' => 1,
                'prefix' => '',
                'suffix' => 'per year',
                'formatted' => '7.9 per year',
            ],
            'XRP/USD' => [
                'value' => '0.0',
                'scale' => 1,
                'prefix' => '',
                'suffix' => '',
                'formatted' => '0.0',
            ],
        ],
        'per_base_currency' => [
            'BTC' => [
                'value' => '0.0',
                'scale' => 1,
                'prefix' => '',
                'suffix' => '',
                'formatted' => '0.0',
            ],
            'ETH' => [
                'value' => '7.9',
                'scale' => 1,
                'prefix' => '',
                'suffix' => 'per year',
                'formatted' => '7.9 per year',
            ],
            'XRP' => [
                'value' => '0.0',
                'scale' => 1,
                'prefix' => '',
                'suffix' => '',
                'formatted' => '0.0',
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'value' => '1.0',
                'scale' => 1,
                'prefix' => '',
                'suffix' => 'per month',
                'formatted' => '1.0 per month',
            ],
        ],
    ],
    'duration' => [
        'global' => [
            'total' => [
                'total' => '17100.00',
                'average' => '3420.00',
                'highest' => [
                    'value' => '7200.00',
                    'pair' => 'BTC/USD',
                ],
                'lowest' => [
                    'value' => '1800.00',
                    'pair' => 'ETH/USD',
                ],
            ],
            'buy' => [
                'total' => '11700.00',
                'average' => '3900.00',
                'highest' => [
                    'value' => '7200.00',
                    'pair' => 'BTC/USD',
                ],
                'lowest' => [
                    'value' => '1800.00',
                    'pair' => 'ETH/USD',
                ],
            ],
            'sell' => [
                'total' => '5400.00',
                'average' => '2700.00',
                'highest' => [
                    'value' => '2700.00',
                    'pair' => 'ETH/USD',
                ],
                'lowest' => [
                    'value' => '2700.00',
                    'pair' => 'ETH/USD',
                ],
            ],
        ],
        'per_pair' => [
            'BTC/USD' => [
                'total' => [
                    'total' => '7200.00',
                    'average' => '7200.00',
                    'highest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '7200.00',
                    'average' => '7200.00',
                    'highest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.00',
                    'average' => '0.00',
                    'highest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH/USD' => [
                'total' => [
                    'total' => '7200.00',
                    'average' => '2400.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '4500.00',
                    'average' => '2250.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '2700.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP/USD' => [
                'total' => [
                    'total' => '2700.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.00',
                    'average' => '0.00',
                    'highest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '2700.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_base_currency' => [
            'BTC' => [
                'total' => [
                    'total' => '7200.00',
                    'average' => '7200.00',
                    'highest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '7200.00',
                    'average' => '7200.00',
                    'highest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '0.00',
                    'average' => '0.00',
                    'highest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                ],
            ],
            'ETH' => [
                'total' => [
                    'total' => '7200.00',
                    'average' => '2400.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '4500.00',
                    'average' => '2250.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '2700.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'XRP' => [
                'total' => [
                    'total' => '2700.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.00',
                    'average' => '0.00',
                    'highest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                    'lowest' => [
                        'value' => '0.00',
                        'pair' => '',
                    ],
                ],
                'sell' => [
                    'total' => '2700.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
        ],
        'per_quote_currency' => [
            'USD' => [
                'total' => [
                    'total' => '17100.00',
                    'average' => '3420.00',
                    'highest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '11700.00',
                    'average' => '3900.00',
                    'highest' => [
                        'value' => '7200.00',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '5400.00',
                    'average' => '2700.00',
                    'highest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2700.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
        ],
    ],
    'streaks' => [
        'wins' => [
            'global' => [
                'total' => '1',
                'buy' => '2',
                'sell' => '0',
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
                'ETH' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => '1',
                    'buy' => '2',
                    'sell' => '0',
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
                'ETH/USD' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
            ],
        ],
        'losses' => [
            'global' => [
                'total' => '2',
                'buy' => '1',
                'sell' => '2',
            ],
            'per_base_currency' => [
                'ETH' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '1',
                ],
                'XRP' => [
                    'total' => '1',
                    'buy' => '0',
                    'sell' => '1',
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => '2',
                    'buy' => '1',
                    'sell' => '2',
                ],
            ],
            'per_pair' => [
                'ETH/USD' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '1',
                ],
                'XRP/USD' => [
                    'total' => '1',
                    'buy' => '0',
                    'sell' => '1',
                ],
            ],
        ],
    ],
]
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Andrej Mihaliak](https://github.com/mihaliak)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
