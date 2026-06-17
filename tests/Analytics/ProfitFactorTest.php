<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitFactor;

it('correctly returns profit factors', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->profitFactor)
        ->toBeInstanceOf(ProfitFactor::class)
        ->total->toString()->toBe('1.58')
        ->forPair('BTC/USD')->toString()->toBe('0.00')
        ->forBaseCurrency('BTC')->toString()->toBe('0.00')
        ->forQuoteCurrency('USD')->toString()->toBe('1.58')
        ->toArray()->toBe([
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
        ]);
})->with('default-trades');

it('correctly returns profit factors for highest and lowest dataset', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->profitFactor)
        ->toBeInstanceOf(ProfitFactor::class)
        ->total->toString()->toBe('9.40')
        ->forPair('BTC/USD')->toString()->toBe('0.00')
        ->forBaseCurrency('BTC')->toString()->toBe('0.00')
        ->forBaseCurrency('SHIB')->toString()->toBe('9.40')
        ->forQuoteCurrency('USD')->toString()->toBe('0.00')
        ->forQuoteCurrency('EUR')->toString()->toBe('1.15')
        ->toArray()->toBe([
            'total' => '9.40',
            'per_pair' => [
                'SHIB/USD' => '0.00',
                'SHIB/EUR' => '1.15',
                'BTC/USD' => '0.00',
            ],
            'per_base_currency' => [
                'SHIB' => '9.40',
                'BTC' => '0.00',
            ],
            'per_quote_currency' => [
                'USD' => '0.00',
                'EUR' => '1.15',
            ],
        ]);
})->with('different-highest-and-lowest');
