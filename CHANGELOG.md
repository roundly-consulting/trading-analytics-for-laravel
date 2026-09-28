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

### Changed

- The facade resolves `TradingAnalyticsManager` (was the `'trading-analytics'` container key
  bound to `AnalyticsFactory`, which is gone); its `make()` is dropped in favour of `for()`.
