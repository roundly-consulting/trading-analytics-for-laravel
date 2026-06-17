<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Counts;

it('correctly returns number of mixed trades after calculation', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->counts)
        ->toBeInstanceOf(Counts::class)
        ->global->total->toString()->toBe('5')
        ->global->buy->toString()->toBe('3')
        ->global->sell->toString()->toBe('2')
        ->forPair('BTC/USD')->total->toString()->toBe('1')
        ->forPair('BTC/USD')->buy->toString()->toBe('1')
        ->forPair('BTC/USD')->sell->toString()->toBe('0')
        ->forPair('ETH/USD')->total->toString()->toBe('3')
        ->forPair('ETH/USD')->buy->toString()->toBe('2')
        ->forPair('ETH/USD')->sell->toString()->toBe('1')
        ->forBaseCurrency('BTC')->total->toString()->toBe('1')
        ->forBaseCurrency('BTC')->buy->toString()->toBe('1')
        ->forBaseCurrency('BTC')->sell->toString()->toBe('0')
        ->forBaseCurrency('ETH')->total->toString()->toBe('3')
        ->forBaseCurrency('ETH')->buy->toString()->toBe('2')
        ->forBaseCurrency('ETH')->sell->toString()->toBe('1')
        ->forQuoteCurrency('USD')->total->toString()->toBe('5')
        ->forQuoteCurrency('USD')->buy->toString()->toBe('3')
        ->forQuoteCurrency('USD')->sell->toString()->toBe('2')
        ->toArray()->toBe([
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
        ]);
})->with('default-trades');

it('correctly returns number of btc trades after calculation', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->counts)
        ->toBeInstanceOf(Counts::class)
        ->global->total->toString()->toBe('3')
        ->global->buy->toString()->toBe('3')
        ->global->sell->toString()->toBe('0')
        ->forPair('BTC/USD')->total->toString()->toBe('3')
        ->forPair('BTC/USD')->buy->toString()->toBe('3')
        ->forPair('BTC/USD')->sell->toString()->toBe('0')
        ->forPair('ETH/USD')->total->toString()->toBe('0')
        ->forPair('ETH/USD')->buy->toString()->toBe('0')
        ->forPair('ETH/USD')->sell->toString()->toBe('0')
        ->forBaseCurrency('BTC')->total->toString()->toBe('3')
        ->forBaseCurrency('BTC')->buy->toString()->toBe('3')
        ->forBaseCurrency('BTC')->sell->toString()->toBe('0')
        ->forBaseCurrency('ETH')->total->toString()->toBe('0')
        ->forBaseCurrency('ETH')->buy->toString()->toBe('0')
        ->forBaseCurrency('ETH')->sell->toString()->toBe('0')
        ->forQuoteCurrency('USD')->total->toString()->toBe('3')
        ->forQuoteCurrency('USD')->buy->toString()->toBe('3')
        ->forQuoteCurrency('USD')->sell->toString()->toBe('0')
        ->toArray()->toBe([
            'global' => [
                'total' => '3',
                'buy' => '3',
                'sell' => '0',
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => '3',
                    'buy' => '3',
                    'sell' => '0',
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => '3',
                    'buy' => '3',
                    'sell' => '0',
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => '3',
                    'buy' => '3',
                    'sell' => '0',
                ],
            ],
        ]);
})->with('btc-buys');
