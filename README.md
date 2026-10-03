<!-- roundly-hero:start -->
<p align="center">
  <a href="https://roundly-consulting.com/open-source/docs/trading-analytics-for-laravel?utm_source=github&utm_medium=readme&utm_campaign=open-source&utm_content=trading-analytics-for-laravel">
    <img src="art/hero.png" alt="Trading Analytics for Laravel — Roundly open source" width="100%">
  </a>
</p>
<!-- roundly-hero:end -->

<!-- roundly-badges:start -->
<p align="center">
  <a href="https://packagist.org/packages/roundly-consulting/trading-analytics-for-laravel"><img src="https://img.shields.io/packagist/v/roundly-consulting/trading-analytics-for-laravel?style=flat-square&label=release" alt="Latest release"></a>
  <a href="https://github.com/roundly-consulting/trading-analytics-for-laravel/actions/workflows/run-tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/roundly-consulting/trading-analytics-for-laravel/run-tests.yml?branch=main&style=flat-square&label=tests" alt="Tests"></a>
  <a href="https://github.com/roundly-consulting/trading-analytics-for-laravel/actions/workflows/fix-php-code-style-issues.yml"><img src="https://img.shields.io/github/actions/workflow/status/roundly-consulting/trading-analytics-for-laravel/fix-php-code-style-issues.yml?branch=main&style=flat-square&label=code%20style" alt="Code style"></a>
  <a href="https://donate.stripe.com/dRmeVe8FX5PF1Qd9pXcEw00"><img src="https://img.shields.io/badge/donate-support%20our%20open%20source-F24E29?style=flat-square&logo=stripe&logoColor=white" alt="Donate"></a>
  <a href="https://www.patreon.com/cw/roundly"><img src="https://img.shields.io/badge/patreon-become%20a%20patron-F96854?style=flat-square&logo=patreon&logoColor=white" alt="Patreon"></a>
  <a href="https://roundly-consulting.com/support-us?utm_source=github&utm_medium=readme&utm_campaign=open-source&utm_content=trading-analytics-for-laravel#crypto"><img src="https://img.shields.io/badge/crypto-BTC%20%C2%B7%20ETH%20%C2%B7%20BNB%20%C2%B7%20SOL-F7931A?style=flat-square&logo=bitcoin&logoColor=white" alt="Crypto"></a>
</p>
<!-- roundly-badges:end -->

# Trading Analytics for Laravel

Calculate trading performance analytics — P&L, returns, drawdown, expectancy, risk-adjusted
ratios and more — for Laravel, with arbitrary-precision `bcmath` arithmetic.

Feed it trades, database rows or an ordered query and it computes a full matrix of trading
performance metrics, broken down globally and per trading pair / base currency / quote currency.
Every amount is computed with `bcmath` and returned as a string-backed value object, so there is
no floating-point drift. The engine makes a single pass over the trades and holds no per-trade
history, so memory stays flat however long the history is.

## Requirements

- PHP `^8.4`
- Laravel `^12.0` or `^13.0`
- The `bcmath` PHP extension

## Installation

```bash
composer require roundly-consulting/trading-analytics-for-laravel
```

The service provider is auto-discovered. The package ships no migrations, views, or commands —
it is a calculation library you use directly in your own code.

Optionally publish the config file to change the default precision or win-rate period:

```bash
php artisan vendor:publish --tag="trading-analytics-config"
```

## Configuration

The package works with zero configuration; publishing the config file only lets you change the
defaults in one place. `config/trading-analytics.php`:

```php
return [
    // Default decimal precision (bcmath scale) for every calculation.
    'scale' => env('TRADING_ANALYTICS_SCALE', 10),

    // Default win-rate bucketing period: daily, weekly, or monthly.
    'win_rate_period' => env('TRADING_ANALYTICS_WIN_RATE_PERIOD', 'daily'),
];
```

| Key | Type | Default | Env var | Purpose |
|---|---|---|---|---|
| `scale` | `int` | `10` | `TRADING_ANALYTICS_SCALE` | Decimal places used when a run does not call `->scale()`. A whole number of at least `0` (`TRADING_ANALYTICS_SCALE=8` works); `ten`, `10.5` or an empty value throw an `InvalidConfigurationException` naming the key. |
| `win_rate_period` | `string` | `daily` | `TRADING_ANALYTICS_WIN_RATE_PERIOD` | Win-rate bucket when a run does not call `->usingWinRatePeriod()`: `daily`, `weekly` or `monthly`. Anything else throws an `InvalidConfigurationException` listing them — it never falls back to `daily`. |

A run's explicit `->scale(...)` / `->usingWinRatePeriod(...)` always overrides the configured
default. Outside a booted Laravel app (no config bound), the built-in defaults (`scale` 10,
`daily`) apply automatically.

## Usage

### Building trades

A `Trade` is an immutable value object. The quickest way to build one is `Trade::make()`, which
takes plain scalars and wraps the numeric fields for you:

```php
use Illuminate\Support\Carbon;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;

$trade = Trade::make(
    baseCurrency: 'BTC',
    quoteCurrency: 'USD',
    openPrice: '45000.00',
    closePrice: '45500.00',
    size: '0.1',
    direction: Direction::BUY,        // or the string 'buy' / 'sell'
    openTime: Carbon::create(2024, 1, 15, 12, 30), // or a parseable date string
    commission: '15.00',              // optional
    closeTime: Carbon::create(2024, 1, 15, 14, 30), // omit for an open position
);
```

Building from an array (e.g. an API payload):

```php
$trade = Trade::fromArray([
    'base_currency' => 'BTC',
    'quote_currency' => 'USD',
    'open_price' => '45000.00',
    'close_price' => '45500.00',
    'size' => '0.1',
    'direction' => 'buy',
    'open_time' => '2024-01-15 12:30:00',
    'commission' => '15.00',          // optional
    'close_time' => '2024-01-15 14:30:00', // optional
]);
```

A missing (or null) required key throws `InvalidTradeException`, as does a field of the wrong type
or an unknown `direction`. Currencies must be non-empty and a realized trade's close time must not
be before its open time.

`Trade::fromRow()` reads a row of any shape your app produces — an array, a query-builder
`stdClass` row, an Eloquent model, any `Arrayable`, or a plain object's public properties:

```php
$trade = Trade::fromRow(DB::table('trades')->find($id));   // stdClass row
$trade = Trade::fromRow(TradeRecord::findOrFail($id));      // your Eloquent model
```

A model is read attribute by attribute, so its casts apply: a `direction` cast to your own
string-backed enum (values `buy` / `sell`) and dates cast to `datetime` or `immutable_datetime`
work as they are. Columns named differently? Expose the field through an accessor — e.g. an
`openTime()` `Attribute` over your `opened_at` column.

`Trade::collect()` maps any iterable of rows (or `Trade`s, or a mix) lazily:

```php
$trades = Trade::collect(DB::table('trades')->orderBy('close_time')->orderBy('id')->lazy());
```

For full control you can still construct a `Trade` directly with `NumericValueAsString` fields:

```php
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString;

$trade = new Trade(
    baseCurrency: 'BTC',
    quoteCurrency: 'USD',
    openPrice: new NumericValueAsString('45000.00'),
    closePrice: new NumericValueAsString('45500.00'),
    size: new NumericValueAsString('0.1'),
    direction: Direction::BUY,
    openTime: Carbon::create(2024, 1, 15, 12, 30),
);
```

### Running the engine

The `TradingAnalytics` facade takes a **trade source**: any iterable of trades or rows (anything
`Trade::fromRow()` reads, `Trade` objects included, or a mix), or a query — see
[Reading trades from your database](#reading-trades-from-your-database). Rows are mapped lazily:

```php
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

// Build, configure, run
$analytics = TradingAnalytics::for($trades)
    ->scale(5)        // decimal places of every amount, default 10 (see Precision)
    ->calculate();    // returns the Analytics instance

// Or build and run in one call, optionally restricted to some metrics
$analytics = TradingAnalytics::calculate(DB::table('trades')->orderBy('close_time')->orderBy('id'));
$analytics = TradingAnalytics::calculate($rows, only: [Counts::class, Streaks::class]);

$analytics->hasBeenCalculated(); // true
$analytics->toArray();           // the full result matrix as a nested array
```

| Facade method | Returns | Purpose |
|---|---|---|
| `for($trades, int $chunk = 1000)` | `Analytics` | build the engine for a trade source, ready to configure |
| `calculate($trades, ?array $only = null, int $chunk = 1000)` | `Analytics` | build and run it, optionally only some calculators |
| `trades($rows, int $chunk = 1000)` | `LazyCollection<int, Trade>` | map a trade source to trades lazily |
| `metrics()` | `list<class-string>` | the calculators the engine runs — what `only` / `except()` accept |
| `using(string $analytics)` | `TradingAnalyticsManager` | build every engine from your `Analytics` subclass (see [Extending](#extending)) |
| `engine()` | `class-string<Analytics>` | the engine class in use |

`$trades` is an iterable, a query builder, an Eloquent builder or a relation; `$chunk` is the page
size when it is a query.

There is no `TradingAnalytics::fake()`: the engine is a pure calculation with no side effects,
so a test feeds it the trades it needs and asserts on the numbers.

#### Without the facade

The facade is a thin layer over `RoundlyConsulting\TradingAnalytics\TradingAnalyticsManager`,
a container singleton. Inject it for the same API, or use the engine class directly:

```php
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsManager;

public function __construct(private TradingAnalyticsManager $analytics) {}

$this->analytics->calculate($rows)->toArray();

// The engine itself — takes a LazyCollection of Trade objects
Analytics::for(Trade::collect($rows))->calculate();
```

The package has no action classes: it is a stateless calculation engine, and the manager only
normalises the input and builds the engine.

### Reading trades from your database

Pass the query itself — a query builder, an Eloquent builder or a relation — and the package
streams it with `lazy()`, holding one page of rows in memory at a time:

```php
use Illuminate\Support\Facades\DB;

TradingAnalytics::calculate(
    DB::table('trades')->where('user_id', $user->id)->orderBy('close_time')->orderBy('id'),
);

// TradeRecord: your own Eloquent model
TradingAnalytics::for(TradeRecord::query()->where('account_id', $accountId)->oldest('close_time')->orderBy('id'));

TradingAnalytics::calculate($account->trades()->orderBy('close_time')->orderBy('id'), chunk: 500);
```

- **Order the query by close time.** The maximum drawdown, the streaks and the running cumulative
  return follow the order trades close in (see [Trade order](#trade-order)), so the package never
  guesses one: a query without an `orderBy` throws `UnorderedTradeSourceException` before anything
  runs — including an Eloquent builder, which Laravel would otherwise quietly order by its primary
  key. Order by close time, with a unique tie-breaker: `->orderBy('close_time')->orderBy('id')`.
- **Chunk size.** `chunk` (default `1000`) is the number of rows per page; a value below 1 throws
  `InvalidChunkSizeException`. Memory scales with the chunk, never with the table.
- **Paging.** `lazy()` pages with `LIMIT` / `OFFSET`, so every page re-runs the ordered query and
  skips the rows before it. On very large tables, order by an indexed column (e.g. an index on
  `(close_time, id)`) so each page stays cheap.
- Your builder is left untouched: the package pages a clone.

Rows come back as `stdClass` (query builder) or models (Eloquent) and are read with
`Trade::fromRow()`, so the columns — or model accessors — must provide the `Trade::fromArray()`
fields.

### Trade order

The maximum drawdown (the realized equity curve), the winning / losing streaks and the running
cumulative return follow the order trades close in. While any of them runs — they all do by
default — realized trades must arrive in close-time order: the first one that closed before the
trade read ahead of it throws `UnorderedTradeSourceException`, instead of reporting figures for a
sequence that never happened. Sorting inside the engine would mean holding the whole history in
memory, which the single pass never does.

- Open trades (no close time) and trades with equal close times may come in any order.
- Order a query with `->orderBy('close_time')->orderBy('id')`; sort an in-memory collection with
  `->sortBy('close_time')->values()` first.
- A run without those calculators (`except([MaxDrawdown::class, Streaks::class,
  GrossCumulativeReturn::class, NetCumulativeReturn::class])`, or an `only()` without them) accepts
  any order. Every other metric — including the trading frequency — is order-independent.

### Running only the metrics you need

Pass the calculator classes you want; their dependencies are pulled in automatically:

```php
use RoundlyConsulting\TradingAnalytics\Analytics\Counts;
use RoundlyConsulting\TradingAnalytics\Analytics\Streaks;

$analytics = TradingAnalytics::calculate($trades, only: [Counts::class]);
$analytics = Analytics::make($trades)->only([Counts::class])->calculate(); // same, on the engine
// $analytics->counts is populated; metrics you didn't request stay null

$analytics = Analytics::make($trades)->except([Streaks::class])->calculate();
```

Every calculator runs on its own: `only()` pulls in what it needs, and what that needs in turn.
Only three calculators depend on another — `ProfitFactor`, `Expectancy` and `RiskRewardRatio` are
built from the realized gross P&L, so they bring `RealizedGrossProfitAndLoss` with them; every
aggregate counts the trades it averages over itself. `except()` still runs an excluded calculator
that a remaining one depends on — `except([RealizedGrossProfitAndLoss::class])` keeps it while the
profit factor needs it. The figures match a full run either way.

Passing a class that is not a registered calculator throws an `UnknownCalculatorException`. To
discover what `only()` / `except()` accept, call `metrics()`:

```php
$available = TradingAnalytics::metrics(); // list of calculator class-strings
```

## Result accessors

After `calculate()`, each metric is available as a property on the `Analytics` instance. Every
metric (where applicable) exposes a `->global` figure plus `->forPair()`, `->forBaseCurrency()`
and `->forQuoteCurrency()` breakdowns, each split into `total`, `buy` and `sell`.

| Accessor | Description |
|---|---|
| `counts` | Number of trades (open and closed), by direction and breakdown. |
| `wins` | Winning closed trades and the win ratio: wins over closed trades. |
| `volume` | Traded size (volume) aggregates, over every trade. |
| `value` | Notional value (size × open price) aggregates, over every trade. |
| `commission` | Commission per trade; a trade without one counts as 0. |
| `unrealizedProfitAndLoss` | Gross & net P&L of the open trades (at their close price), averaged over them. |
| `realizedProfitAndLoss` | Gross & net P&L of the closed trades, averaged over them. |
| `profitFactor` | Realized gross profit divided by realized gross loss (0 without a loss). |
| `cumulativeReturn` | Compounded return in percent (gross & net), its geometric mean per trade, and the highest / lowest running return. |
| `frequency` | How often trades are opened (per hour/day/week/…), over the span of open times. |
| `duration` | How long closed trades stayed open, in seconds. |
| `streaks` | Longest runs of winning and of losing closed trades. |
| `expectancy` | Expected P&L of an average closed trade. |
| `riskRewardRatio` | Average win divided by average loss. |
| `winRateByPeriod` | Win rate of closed trades, bucketed by the day / week / month they opened. |
| `maxDrawdown` | Largest peak-to-trough drop of the realized (net) equity curve. |
| `riskAdjustedReturns` | Sharpe and Sortino ratios of the realized net returns. |

Every aggregate (`total`, `average`, `highest`, `lowest`) also carries a `count` — the trades it
was built from — and its average divides by exactly those: realized figures by the closed trades,
unrealized ones by the open trades.

A break-even trade (P&L exactly 0) is a closed trade that neither won nor lost: it counts towards
the win ratio's denominator and the expectancy, but not towards the average loss of the
risk/reward ratio, and it ends both the winning and the losing streak. Fewer than two trades, or
trades that all opened in the same second, leave the frequency at `0` with no unit; a figure with
nothing to divide (no loss for the profit factor or risk/reward ratio, no deviation for Sharpe or
Sortino) stays `0`.

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

### Precision

`->scale()` (or the `scale` config key) sets the decimal places of every amount: P&L, volume,
value, commission, their averages, the expectancy's and risk/reward ratio's average win / loss,
and the drawdown. bcmath truncates rather than rounds, so each figure is truncated to its scale
as it is stored; ratios are divided at a higher working scale first, so an operand below the
result's precision (a loss of 0.00005 BTC, a deviation of a few basis points) still counts.
Ratios and percentages are reported at a fixed scale, whatever the run's:

| Figure | Decimal places |
|---|---|
| counts, wins, streaks | 0 |
| `frequency` | 1 |
| `wins->winRatio`, `profitFactor` | 2 |
| `cumulativeReturn` (percent), `duration` (seconds) | 2 |
| `expectancy->winRate` / `lossRate`, `winRateByPeriod->rates` | 4 |
| `riskRewardRatio->value`, `maxDrawdown->percentage`, `sharpeRatio`, `sortinoRatio` | 4 |
| `riskAdjustedReturns` mean / deviations | 10 |

A trade's own numbers keep the scale they were built with — 10 decimal places through
`Trade::make()`, `fromArray()` and `fromRow()`; construct the `NumericValueAsString` fields
yourself (`NumericValueAsString::of($size, scale: 18)`) when a price × size needs more.

### JSON & API responses

The `Analytics` instance and every result object implement Laravel's `Arrayable` and `Jsonable`
contracts plus PHP's `JsonSerializable`, so they drop straight into API responses:

```php
// In a controller — returns the full result matrix as JSON
return $analytics;

// Or explicitly
return response()->json($analytics);

$analytics->toArray();   // nested array
$analytics->toJson();    // JSON string
json_encode($analytics); // same payload via JsonSerializable
```

Each individual result (e.g. `$analytics->expectancy`, `$analytics->counts`) and the
`NumericValueAsString` value object serialize the same way.

### Win rate by period

```php
use RoundlyConsulting\TradingAnalytics\Enums\Period;

$analytics = Analytics::make($trades)
    ->usingWinRatePeriod(Period::MONTHLY) // DAILY (default), WEEKLY, MONTHLY
    ->calculate();

$analytics->winRateByPeriod->rates;                 // ['2024-01' => NumericValueAsString, ...]
$analytics->winRateByPeriod->rates['2024-01']->toRawString(); // '0.6666'
$analytics->winRateByPeriod->toArray()['rates'];   // ['2024-01' => '0.6666', ...]
```

### Working with `NumericValueAsString`

All numeric results are `NumericValueAsString` value objects backed by a precise `bcmath` string.
Build one with the `::of()` named constructor (cleaner than `new`):

```php
NumericValueAsString::of('1.005')->round(2)->toRawString();           // '1.01' — half away from zero
NumericValueAsString::of('1.005', scale: 2)->toRawString();           // '1.00' — the scale truncates
NumericValueAsString::of('1e-5')->toRawString();                      // '0.0000100000'

$value = NumericValueAsString::of('10', scale: 2);
$value->add(1)->subtract('0.5')->multiply(2)->toRawString();          // '21.00' — mutates $value
$value->add(1, immutable: true)->toRawString();                       // '22.00' — $value stays '21.00'
(string) $value->withSuffix('USD');                                   // '21.00 USD'
$value->toFloat();                                                    // 21.0, when you explicitly want a float
```

Operations change the value in place and return it, so they chain; pass `immutable: true` for a
new instance instead. Input is read exactly: numeric strings (padding allowed), integers, floats
and exponent notation (`'1.5e-3'`, `0.00001`) are expanded into plain decimals before bcmath sees
them.

Format a result for display without mutating the stored value using the immutable `withPrefix()` /
`withSuffix()` helpers, which return a new instance:

```php
$commission = $analytics->commission->global->total->total;  // NumericValueAsString, e.g. '25.0000000000'

$commission->withPrefix('$')->toString();            // '$ 25.0000000000'
$commission->withPrefix('$')->round(2)->toString();  // '$ 25.00' — the clone is rounded, not the result
```

Dividing by zero throws a `DivisionByZeroException`; non-numeric input (`'abc'`, `INF`, `NAN`) or
an exponent above 1000 throws an `InvalidNumericOperationException`; a negative scale throws an
`InvalidScaleException`.

## Extending

`Analytics` and the calculators in `RoundlyConsulting\TradingAnalytics\Analytics\*` are designed
to be extended — subclass `Analytics` to register custom calculators, and tell the facade to build
your subclass (once, e.g. in a service provider's `boot()`; the manager is a singleton):

```php
use App\Analytics\DeskAnalytics; // extends RoundlyConsulting\TradingAnalytics\Analytics

TradingAnalytics::using(DeskAnalytics::class);

TradingAnalytics::calculate($trades); // a DeskAnalytics instance
```

`using()` throws `InvalidEngineException` for a class that isn't `Analytics` or a subclass of it.

Or replace the per-trade / after-trades hooks per instance. A closure runs **instead of** each
calculator's own hook, so call the hook yourself to keep the default figures — leave it out and
the metrics stay at zero:

```php
$analytics = Analytics::make($trades)
    ->onEachTrade(function (Analytics $analytics, string $calculator, Trade $trade) {
        // observe, skip or adjust the trade for this calculator, then run it
        $calculator::calculatePerTrade($analytics, $trade);
    })
    ->afterTrades(function (Analytics $analytics, string $calculator) {
        $calculator::calculateAfterTrades($analytics);
    })
    ->calculate();
```

Sharpe and Sortino use the separate multi-pass hooks: each realized return is folded into running
sums (count, sum, sum of squares, sum of squared shortfalls) during the pass, and the mean,
population standard deviation and downside deviation are derived from those sums afterwards — no
return series is stored.

## Exceptions

Every exception extends `RoundlyConsulting\TradingAnalytics\Exceptions\TradingAnalyticsException`,
so you can catch them all with one `catch`:

- `InvalidTradeException` — empty currency, close-before-open, a missing or null required field, a field of the wrong type, or an invalid direction.
- `UnorderedTradeSourceException` — a query passed as a trade source without an `orderBy`, or a
  realized trade that arrives after one that closed later (see [Trade order](#trade-order)).
- `InvalidChunkSizeException` — a `chunk` below 1.
- `InvalidScaleException` — negative scale.
- `InvalidNumericOperationException` — non-numeric input (`INF` / `NAN` included), an exponent
  above 1000, or a fractional `pow()` exponent.
- `DivisionByZeroException` — division by zero.
- `UnknownCalculatorException` — `only()` / `except()` / `calculate(only: …)` given a non-calculator class.
- `InvalidEngineException` — `TradingAnalytics::using()` given a class that isn't an `Analytics` engine.

## Integrates with

- [`enums-for-laravel`](https://github.com/roundly-consulting/enums-for-laravel) — the package's
  `Direction` (buy/sell) and `Period` (daily/weekly/monthly) enums adopt the shared enum helper
  toolkit, so they expose a full select/validation surface out of the box:

  ```php
  Direction::validationRule();   // "in:buy,sell"
  Period::validationRule();      // "in:daily,weekly,monthly"

  Period::toOptions()->all();    // ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly']
  Direction::options();          // Collection<EnumOption{value,label,name}> for JS/Inertia selects
  Period::labels()->all();       // ['Daily', 'Weekly', 'Monthly']
  ```

  The domain methods stay intact — `Direction::isBuy()` / `isSell()` and `Period::bucketFor()`.

- [`package-toolkit-for-laravel`](https://github.com/roundly-consulting/package-toolkit-for-laravel) —
  the service provider is built on the shared package builder, so the config file, the
  `trading-analytics-config` publish tag and the container bindings are declared in one place. The
  package also reports its configured scale and win-rate period to `php artisan about`:

  ```bash
  php artisan about --only=trading-analytics
  ```

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for what has changed recently.

## Contributing

Contributions are welcome. Please open an issue or pull request on the
[repository](https://github.com/roundly-consulting/trading-analytics-for-laravel).

<!-- roundly-support:start -->
## Support our work

This package is free and open source, built and maintained by
[Roundly Consulting](https://roundly-consulting.com/open-source?utm_source=github&utm_medium=readme&utm_campaign=open-source&utm_content=trading-analytics-for-laravel).
If it saves you time, please consider supporting our open-source work — a one-time donation, a
monthly pledge on Patreon or a crypto donation helps fund maintenance, new features and new
packages.

<a href="https://donate.stripe.com/dRmeVe8FX5PF1Qd9pXcEw00"><img src="https://img.shields.io/badge/Donate-Support%20Roundly%20open%20source-F24E29?style=for-the-badge&logo=stripe&logoColor=white" alt="Donate to Roundly open source"></a>
<a href="https://www.patreon.com/cw/roundly"><img src="https://img.shields.io/badge/Patreon-Become%20a%20patron-F96854?style=for-the-badge&logo=patreon&logoColor=white" alt="Become a patron on Patreon"></a>
<a href="https://roundly-consulting.com/support-us?utm_source=github&utm_medium=readme&utm_campaign=open-source&utm_content=trading-analytics-for-laravel#crypto"><img src="https://img.shields.io/badge/Crypto-BTC%20%C2%B7%20ETH%20%C2%B7%20BNB%20%C2%B7%20SOL-F7931A?style=for-the-badge&logo=bitcoin&logoColor=white" alt="Donate crypto: BTC, ETH, BNB or SOL"></a>
<!-- roundly-support:end -->

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
