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

Trading performance analytics for Laravel — P&L, returns, drawdown, expectancy, streaks, Sharpe and
Sortino ratios and more, globally and per pair, base and quote currency. Feed it trades, rows or an
ordered query; every amount is computed with `bcmath` in a single pass, so there is no float drift
and memory stays flat however long the history is.

## Installation

Requires PHP 8.4 with `ext-bcmath`, and Laravel 12 or 13.

```bash
composer require roundly-consulting/trading-analytics-for-laravel
```

## Usage

Build trades from plain values (or read your own rows with `Trade::fromRow()`):

```php
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;

$trades = [
    Trade::make(baseCurrency: 'BTC', quoteCurrency: 'USD', openPrice: '45000.00', closePrice: '45500.00',
        size: '0.1', direction: Direction::BUY, openTime: '2024-01-15 12:30', commission: '15.00', closeTime: '2024-01-15 14:30'),
    Trade::make(baseCurrency: 'ETH', quoteCurrency: 'USD', openPrice: '2500.00', closePrice: '2450.00',
        size: '2', direction: Direction::BUY, openTime: '2024-01-16 09:00', closeTime: '2024-01-16 11:00'),
];
```

Run the engine over them:

```php
use Illuminate\Support\Facades\DB;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;

$analytics = TradingAnalytics::for($trades)->scale(2)->calculate();

// …or stream a query ordered by close time, one page of rows at a time:
$history = TradingAnalytics::calculate(
    DB::table('trades')->where('user_id', $user->id)->orderBy('close_time')->orderBy('id'),
);
```

Read the figures — exact `bcmath` strings, ready to return as JSON:

```php
$analytics->realizedProfitAndLoss->net->global->total->total->toRawString();   // "-65.00"
$analytics->wins->winRatio->global->total->toRawString();                       // "0.50"
$analytics->volume->forPair('BTC/USD')->buy->total->toRawString();              // "0.10"
$analytics->maxDrawdown->percentage;
$analytics->riskAdjustedReturns->sharpeRatio;

return $analytics;   // the full result matrix as JSON
```

<!-- roundly-docs:start -->
## Documentation

The full documentation — configuration, every feature and its API, and testing — lives on our
website: **[roundly-consulting.com/open-source/docs/trading-analytics-for-laravel](https://roundly-consulting.com/open-source/docs/trading-analytics-for-laravel?utm_source=github&utm_medium=readme&utm_campaign=open-source&utm_content=trading-analytics-for-laravel)**

Release notes are in [CHANGELOG.md](CHANGELOG.md). To contribute, see the
[contributing guide](https://github.com/roundly-consulting/.github/blob/main/CONTRIBUTING.md).
<!-- roundly-docs:end -->

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
