<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Wins;

it('correctly returns number of winning trades after calculation', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->wins)
        ->toBeInstanceOf(Wins::class)
        ->global->total->toString()->toBe('2')
        ->global->buy->toString()->toBe('2')
        ->global->sell->toString()->toBe('0')
        ->forPair('BTC/USD')->total->toString()->toBe('1')
        ->forPair('BTC/USD')->buy->toString()->toBe('1')
        ->forPair('BTC/USD')->sell->toString()->toBe('0')
        ->forPair('ETH/USD')->total->toString()->toBe('1')
        ->forPair('ETH/USD')->buy->toString()->toBe('1')
        ->forPair('ETH/USD')->sell->toString()->toBe('0')
        ->forBaseCurrency('BTC')->total->toString()->toBe('1')
        ->forBaseCurrency('BTC')->buy->toString()->toBe('1')
        ->forBaseCurrency('BTC')->sell->toString()->toBe('0')
        ->forBaseCurrency('ETH')->total->toString()->toBe('1')
        ->forBaseCurrency('ETH')->buy->toString()->toBe('1')
        ->forBaseCurrency('ETH')->sell->toString()->toBe('0')
        ->forQuoteCurrency('USD')->total->toString()->toBe('2')
        ->forQuoteCurrency('USD')->buy->toString()->toBe('2')
        ->forQuoteCurrency('USD')->sell->toString()->toBe('0')
        ->winRatio->global->total->toString()->toBe('0.40')
        ->winRatio->global->buy->toString()->toBe('0.66')
        ->winRatio->global->sell->toString()->toBe('0.00')
        ->winRatio->forPair('BTC/USD')->total->toString()->toBe('1.00')
        ->winRatio->forPair('BTC/USD')->buy->toString()->toBe('1.00')
        ->winRatio->forPair('BTC/USD')->sell->toString()->toBe('0.00')
        ->winRatio->forPair('ETH/USD')->total->toString()->toBe('0.33')
        ->winRatio->forPair('ETH/USD')->buy->toString()->toBe('0.50')
        ->winRatio->forPair('ETH/USD')->sell->toString()->toBe('0.00')
        ->winRatio->forBaseCurrency('BTC')->total->toString()->toBe('1.00')
        ->winRatio->forBaseCurrency('BTC')->buy->toString()->toBe('1.00')
        ->winRatio->forBaseCurrency('BTC')->sell->toString()->toBe('0.00')
        ->winRatio->forBaseCurrency('ETH')->total->toString()->toBe('0.33')
        ->winRatio->forBaseCurrency('ETH')->buy->toString()->toBe('0.50')
        ->winRatio->forBaseCurrency('ETH')->sell->toString()->toBe('0.00')
        ->winRatio->forQuoteCurrency('USD')->total->toString()->toBe('0.40')
        ->winRatio->forQuoteCurrency('USD')->buy->toString()->toBe('0.66')
        ->winRatio->forQuoteCurrency('USD')->sell->toString()->toBe('0.00')
        ->toArray()->toBe([
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
                    // XRP's one closed trade lost: a real 0.00, listed like any other ratio.
                    'XRP' => [
                        'total' => '0.00',
                        'buy' => '0.00',
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
                    'XRP/USD' => [
                        'total' => '0.00',
                        'buy' => '0.00',
                        'sell' => '0.00',
                    ],
                ],
            ],
        ]);
})->with('default-trades');

it('correctly returns number of winning btc trades after calculation', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->wins)
        ->toBeInstanceOf(Wins::class)
        ->global->total->toString()->toBe('1')
        ->global->buy->toString()->toBe('1')
        ->global->sell->toString()->toBe('0')
        ->forPair('BTC/USD')->total->toString()->toBe('1')
        ->forPair('BTC/USD')->buy->toString()->toBe('1')
        ->forPair('BTC/USD')->sell->toString()->toBe('0')
        ->forPair('ETH/USD')->total->toString()->toBe('0')
        ->forPair('ETH/USD')->buy->toString()->toBe('0')
        ->forPair('ETH/USD')->sell->toString()->toBe('0')
        ->forBaseCurrency('BTC')->total->toString()->toBe('1')
        ->forBaseCurrency('BTC')->buy->toString()->toBe('1')
        ->forBaseCurrency('BTC')->sell->toString()->toBe('0')
        ->forBaseCurrency('ETH')->total->toString()->toBe('0')
        ->forBaseCurrency('ETH')->buy->toString()->toBe('0')
        ->forBaseCurrency('ETH')->sell->toString()->toBe('0')
        ->forQuoteCurrency('USD')->total->toString()->toBe('1')
        ->forQuoteCurrency('USD')->buy->toString()->toBe('1')
        ->forQuoteCurrency('USD')->sell->toString()->toBe('0')
        ->winRatio->global->total->toString()->toBe('0.33')
        ->winRatio->global->buy->toString()->toBe('0.33')
        ->winRatio->global->sell->toString()->toBe('0.00')
        ->winRatio->forPair('BTC/USD')->total->toString()->toBe('0.33')
        ->winRatio->forPair('BTC/USD')->buy->toString()->toBe('0.33')
        ->winRatio->forPair('BTC/USD')->sell->toString()->toBe('0.00')
        ->winRatio->forPair('ETH/USD')->total->toString()->toBe('0.00')
        ->winRatio->forPair('ETH/USD')->buy->toString()->toBe('0.00')
        ->winRatio->forPair('ETH/USD')->sell->toString()->toBe('0.00')
        ->winRatio->forBaseCurrency('BTC')->total->toString()->toBe('0.33')
        ->winRatio->forBaseCurrency('BTC')->buy->toString()->toBe('0.33')
        ->winRatio->forBaseCurrency('BTC')->sell->toString()->toBe('0.00')
        ->winRatio->forBaseCurrency('ETH')->total->toString()->toBe('0.00')
        ->winRatio->forBaseCurrency('ETH')->buy->toString()->toBe('0.00')
        ->winRatio->forBaseCurrency('ETH')->sell->toString()->toBe('0.00')
        ->winRatio->forQuoteCurrency('USD')->total->toString()->toBe('0.33')
        ->winRatio->forQuoteCurrency('USD')->buy->toString()->toBe('0.33')
        ->winRatio->forQuoteCurrency('USD')->sell->toString()->toBe('0.00')
        ->toArray()->toBe([
            'global' => [
                'total' => '1',
                'buy' => '1',
                'sell' => '0',
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => '1',
                    'buy' => '1',
                    'sell' => '0',
                ],
            ],
            'win_ratio' => [
                'global' => [
                    'total' => '0.33',
                    'buy' => '0.33',
                    'sell' => '0.00',
                ],
                'per_base_currency' => [
                    'BTC' => [
                        'total' => '0.33',
                        'buy' => '0.33',
                        'sell' => '0.00',
                    ],
                ],
                'per_quote_currency' => [
                    'USD' => [
                        'total' => '0.33',
                        'buy' => '0.33',
                        'sell' => '0.00',
                    ],
                ],
                'per_pair' => [
                    'BTC/USD' => [
                        'total' => '0.33',
                        'buy' => '0.33',
                        'sell' => '0.00',
                    ],
                ],
            ],
        ]);
})->with('btc-buys');
