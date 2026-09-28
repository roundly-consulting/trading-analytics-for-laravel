# Changelog

All notable changes to `trading-analytics-for-laravel` are documented in this file. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[Semantic Versioning](https://semver.org/).

## Unreleased

Initial public release.

### Added

- A trading-performance engine: feed `Analytics::make($trades)->calculate()` a collection of
  `Trade` objects and get the full metric matrix.
- Arbitrary-precision `bcmath` arithmetic with a configurable scale; every figure is a
  `NumericValueAsString` value object, so there is no floating-point drift.
- Metrics including counts, win ratios, volume, value, commission, realized and unrealized P&L,
  profit factor, cumulative return, frequency, duration, streaks, expectancy and risk/reward.
- Maximum drawdown, Sharpe and Sortino ratios, and win rate by day, week or month.
- Every metric broken down globally and per trading pair, base currency and quote currency,
  each split into total, buy and sell.
- `Trade::make()` and array ingestion with validation, plus `Trade::collect()` to stream trades
  lazily from the database.
- `only()` / `except()` to run just the metrics you need, with dependencies resolved automatically.
- JSON-ready results (`Arrayable`, `Jsonable`, `JsonSerializable`) for API responses.
- Extension points: custom calculators and per-trade / after-trades hooks.
- The `TradingAnalytics` facade over an injectable `TradingAnalyticsManager`:
  `for($trades)` and `calculate($trades, only: [...])` accept `Trade` objects, rows or a mix;
  `trades($rows)`, `metrics()`, and `using(MyAnalytics::class)` / `engine()` to build every engine
  from your `Analytics` subclass (`InvalidEngineException` otherwise).
- `Direction` / `Period` enums with select and validation helpers.
- Query sources: `TradingAnalytics::for()` / `calculate()` / `trades()` accept a query builder, an
  Eloquent builder or a relation and stream it with `lazy($chunk)` (new `chunk` argument, default
  1000). A query without an `orderBy` throws `UnorderedTradeSourceException` (the metrics are
  order-sensitive, so no order is guessed); a chunk below 1 throws `InvalidChunkSizeException`.
- `Trade::fromRow()` reads arrays, query-builder `stdClass` rows, Eloquent models (through their
  casts and accessors), `Arrayable`s and plain objects; `Trade::collect()` and the facade use it.
  `Trade::make()` takes any `DateTimeInterface` and any string-backed enum direction.

### Changed

- Sharpe and Sortino are computed from running sums in constant memory. `RiskAdjustedReturns`
  (the result) no longer has a `$returns` list: it exposes `$sampleSize`, `$sumOfReturns`,
  `$sumOfSquaredReturns` and `$sumOfSquaredShortfalls`, and `recordReturn()` folds a return in
  instead of storing it. Every reported figure is unchanged.
- A null required field or a field of the wrong type throws `InvalidTradeException` (was a
  `TypeError`).
- The facade resolves `TradingAnalyticsManager` (was the `'trading-analytics'` container key
  bound to `AnalyticsFactory`, which is gone); its `make()` is dropped in favour of `for()`.

### Fixed

- The README's `Trade::collect(DB::table('trades')->lazy())` example crashed twice over: rows are
  `stdClass`, which only arrays were accepted as, and `lazy()` refuses an unordered query.
- The average cumulative return no longer stalls short of its root: `BcMath::nthRoot()` seeded
  Newton's method at 1, so a large growth factor (e.g. 1,000× over 1,000 trades) came back far too
  high. Its intermediates also grew with the trade count (a 50,000-trade root took ~17 s); they now
  stay at a fixed scale.
- `only([...])` / `calculate(only: [...])` crashed for 11 of the 20 calculators (`Attempt to read
  property "global" on null`): every calculator on the directional-aggregates base divides by the
  counts without declaring `Counts` as a dependency, and dependencies were resolved one level deep
  only. Dependencies are now declared and resolved transitively, and `except()` keeps a
  dependency a remaining calculator still needs.
- Exponent notation and floats no longer crash inside bcmath (`ValueError: bcadd(): Argument #1
  is not well-formed`): `NumericValueAsString::of('1e-5')`, `Trade::make(size: 0.00001)` and
  padded strings like `' 12.5 '` are expanded exactly into plain decimals. `INF` / `NAN` and an
  exponent above 1000 throw `InvalidNumericOperationException`.
- Realized and unrealized profits and losses no longer leak into each other: the profit/loss
  split ran for every trade, so each realized trade also landed in the unrealized
  `grossProfits` / `grossLosses` / `netProfits` / `netLosses` and each open one in the realized.
  A break-even trade now lands in neither.
- The profit factor is realized gross profit over realized gross loss (it read the unrealized
  aggregates), and a loss-only pair or currency reads `0.00` instead of going missing.
- Averages divide by the trades that fed them: realized P&L and trade duration over the closed
  trades, unrealized P&L over the open ones (all three divided by every trade). Each
  `NumericAggregates` now carries that `count`, so the aggregate calculators no longer depend on
  `Counts`.
- A zero is a value, not an "unset" marker: a break-even trade, a same-second trade or a trade
  without a commission no longer resets the highest / lowest, the cumulative return can report a
  highest below 0, a −100 % trade is no longer restarted by the next one, and a pair whose P&L
  nets to exactly 0 still appears in `toArray()`.
- A trade without a commission counts as a zero commission (as the net P&L already read it), so
  it shows in the commission extremes and breakdowns as well as the per-trade average.
- `wins` and the win ratio count closed trades only (open trades counted as wins and in the
  denominator), so the win ratio agrees with `expectancy->winRate`; `Wins` no longer depends on
  `Counts`. The ratio is listed for every key with a closed trade, a `0.00` included.
- A break-even trade is neither a win nor a loss: it no longer dilutes the average loss of the
  risk/reward ratio and the expectancy (new `Expectancy::$breakEvenTrades`), and it ends both
  the winning and the losing streak instead of extending a losing one.
- Expectancy is (realized gross profit − gross loss) / closed trades at full precision (it was
  built from win / loss rates truncated to 4 decimals: 26.664 instead of 26.6666666666).
- Trading frequency no longer divides by zero (trades opened in the same second, or date-only
  open times on one day, crashed every default run) and no longer reads `0.0 per hour` for
  unsorted input: it is measured over the span of open times, in any order. No measurable gap
  leaves it at 0 with no unit, like a single trade.
- A currency that is the base of one pair and the quote of another (BTC in BTC/USDT and ETH/BTC)
  no longer shares one frequency and one running streak between its two breakdowns.
