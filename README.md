# Trading Analytics for Laravel

Calculate trading performance analytics — P&L, returns, drawdown, expectancy, risk-adjusted
ratios and more — for Laravel, with arbitrary-precision `bcmath` arithmetic.

Feed it a `LazyCollection` of `Trade` objects and it computes a full matrix of trading
performance metrics, broken down globally and per trading pair / base currency / quote currency.
Every amount is computed with `bcmath` and returned as a string-backed value object, so there is
no floating-point drift.

## Requirements

- PHP `^8.3`
- Laravel `^12.0` or `^13.0`
- The `bcmath` PHP extension

## Installation

```bash
composer require roundly-consulting/trading-analytics-for-laravel
```

The service provider is auto-discovered. The package ships **no** config file, migrations, views,
or commands — it is a calculation library you use directly in your own code.

## Usage

### Building trades

A `Trade` is an immutable value object. Currencies must be non-empty and, for a realized trade,
the close time must not be before the open time — otherwise an `InvalidTradeException` is thrown.

```php
use Illuminate\Support\Carbon;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;

$trade = new Trade(
    baseCurrency: 'BTC',
    quoteCurrency: 'USD',
    openPrice: new NumericValueAsString('45000.00'),
    closePrice: new NumericValueAsString('45500.00'),
    size: new NumericValueAsString('0.1'),
    direction: Direction::BUY,
    openTime: Carbon::create(2024, 1, 15, 12, 30),
    commission: new NumericValueAsString('15.00'), // optional
    closeTime: Carbon::create(2024, 1, 15, 14, 30), // omit for an open position
);
```

Stream trades lazily from the database so the whole history never sits in memory at once:

```php
use Illuminate\Support\LazyCollection;

$trades = Trade::query()->lazy()->map(fn ($row) => new Trade(/* ... */));

// or build them with a generator
$trades = LazyCollection::make(function () {
    yield new Trade(/* ... */);
    yield new Trade(/* ... */);
});
```

### Running the engine

```php
use RoundlyConsulting\TradingAnalytics\Analytics;

$analytics = Analytics::make($trades)
    ->scale(5)        // arbitrary precision (decimal places), default 10
    ->calculate();    // returns the Analytics instance

$analytics->hasBeenCalculated(); // true
$analytics->toArray();           // the full result matrix as a nested array
```

`Analytics::for($trades)` is an alias for `make()`. You can also resolve the optional facade:

```php
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

$analytics = TradingAnalytics::make($trades)->calculate();
```

### Running only the metrics you need

Pass the calculator classes you want; their dependencies are pulled in automatically:

```php
use RoundlyConsulting\TradingAnalytics\Analytics\Counts;
use RoundlyConsulting\TradingAnalytics\Analytics\Streaks;

$analytics = Analytics::make($trades)->only([Counts::class])->calculate();
// $analytics->counts is populated; metrics you didn't request stay null

$analytics = Analytics::make($trades)->except([Streaks::class])->calculate();
```

Passing a class that is not a registered calculator throws an `UnknownCalculatorException`.

## Result accessors

After `calculate()`, each metric is available as a property on the `Analytics` instance. Every
metric (where applicable) exposes a `->global` figure plus `->forPair()`, `->forBaseCurrency()`
and `->forQuoteCurrency()` breakdowns, each split into `total`, `buy` and `sell`.

| Accessor | Description |
|---|---|
| `counts` | Number of trades, by direction and breakdown. |
| `wins` | Winning-trade counts and win ratios. |
| `volume` | Traded size (volume) aggregates. |
| `value` | Notional value aggregates. |
| `commission` | Commission totals and averages. |
| `unrealizedProfitAndLoss` | Gross & net P&L for open trades. |
| `realizedProfitAndLoss` | Gross & net P&L for closed trades. |
| `profitFactor` | Gross profit divided by gross loss. |
| `cumulativeReturn` | Geometric cumulative return (gross & net). |
| `frequency` | How often trades are placed (per hour/day/week/…). |
| `duration` | How long realized trades stay open. |
| `streaks` | Longest winning and losing streaks. |
| `expectancy` | Expected value of an average trade. |
| `riskRewardRatio` | Average win divided by average loss. |
| `winRateByPeriod` | Win rate bucketed by day / week / month. |
| `maxDrawdown` | Largest peak-to-trough equity drop. |
| `riskAdjustedReturns` | Sharpe and Sortino ratios. |

```php
// Breakdown example
$analytics->wins->global->total;             // NumericValueAsString
$analytics->volume->forPair('BTC/USD')->buy->total;
$analytics->realizedProfitAndLoss->net->global->total->average;

// New metrics
$analytics->expectancy->value;               // (winRate * avgWin) - (lossRate * avgLoss)
$analytics->riskRewardRatio->value;
$analytics->maxDrawdown->percentage;
$analytics->riskAdjustedReturns->sharpeRatio;
$analytics->riskAdjustedReturns->sortinoRatio;
```

### Win rate by period

```php
use RoundlyConsulting\TradingAnalytics\Enums\Period;

$analytics = Analytics::make($trades)
    ->usingWinRatePeriod(Period::MONTHLY) // DAILY (default), WEEKLY, MONTHLY
    ->calculate();

$analytics->winRateByPeriod->rates; // ['2024-01' => '0.6666', ...]
```

### Working with `NumericValueAsString`

All numeric results are `NumericValueAsString` value objects backed by a precise `bcmath` string:

```php
$value = new NumericValueAsString('1.005', scale: 2);

$value->add(1)->subtract('0.5')->multiply(2); // chainable bcmath operations
$value->round(2)->toRawString();              // '1.01' — true half-away-from-zero rounding
(string) $value;                              // formatted, with optional prefix/suffix
$value->toFloat();                            // float, when you explicitly want one
```

Dividing by zero throws a `DivisionByZeroException`, passing a non-numeric string throws an
`InvalidNumericOperationException`, and a negative scale throws an `InvalidScaleException`.

## Extending

`Analytics` and the calculators in `RoundlyConsulting\TradingAnalytics\Analytics\*` are designed
to be extended — subclass `Analytics` to register custom calculators, or override the per-trade /
after-trades hooks per instance:

```php
$analytics = Analytics::make($trades)
    ->onEachTrade(function (Analytics $analytics, string $calculator, Trade $trade) {
        // observe or customise each calculator per trade
    })
    ->afterTrades(function (Analytics $analytics, string $calculator) {
        // run after all trades have been processed
    })
    ->calculate();
```

Sharpe and Sortino are computed on a separate multi-pass path (they need the full per-trade return
series for variance); the single-pass aggregate calculators stay free of that concern.

## Exceptions

Every exception extends `RoundlyConsulting\TradingAnalytics\Exceptions\TradingAnalyticsException`,
so you can catch them all with one `catch`:

- `InvalidTradeException` — empty currency or close-before-open.
- `InvalidScaleException` — negative scale.
- `InvalidNumericOperationException` — non-numeric input or a fractional `pow()` exponent.
- `DivisionByZeroException` — division by zero.
- `UnknownCalculatorException` — `only()` / `except()` given a non-calculator class.

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for what has changed recently.

## Contributing

Contributions are welcome. Please open an issue or pull request on the
[repository](https://github.com/roundly-consulting/trading-analytics-for-laravel).

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
