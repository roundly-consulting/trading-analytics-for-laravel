<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\Streaks;

it('correctly returns winning and loosing streaks', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->streaks)
        ->toBeInstanceOf(Streaks::class)
        ->wins->global->total->toString()->toBe('3')
        ->wins->global->buy->toString()->toBe('4')
        ->wins->global->sell->toString()->toBe('0')
        ->wins->forPair('BTC/USD')->total->toString()->toBe('3')
        ->wins->forPair('BTC/USD')->buy->toString()->toBe('3')
        ->wins->forPair('BTC/USD')->sell->toString()->toBe('0')
        ->wins->forPair('ETH/USD')->total->toString()->toBe('1')
        ->wins->forPair('ETH/USD')->buy->toString()->toBe('1')
        ->wins->forPair('ETH/USD')->sell->toString()->toBe('0')
        ->wins->forBaseCurrency('BTC')->total->toString()->toBe('3')
        ->wins->forBaseCurrency('BTC')->buy->toString()->toBe('3')
        ->wins->forBaseCurrency('BTC')->sell->toString()->toBe('0')
        ->wins->forBaseCurrency('ETH')->total->toString()->toBe('1')
        ->wins->forBaseCurrency('ETH')->buy->toString()->toBe('1')
        ->wins->forBaseCurrency('ETH')->sell->toString()->toBe('0')
        ->wins->forQuoteCurrency('USD')->total->toString()->toBe('3')
        ->wins->forQuoteCurrency('USD')->buy->toString()->toBe('4')
        ->wins->forQuoteCurrency('USD')->sell->toString()->toBe('0')
        ->losses->global->total->toString()->toBe('1')
        ->losses->global->buy->toString()->toBe('0')
        ->losses->global->sell->toString()->toBe('2')
        ->losses->forPair('BTC/USD')->total->toString()->toBe('1')
        ->losses->forPair('BTC/USD')->buy->toString()->toBe('0')
        ->losses->forPair('BTC/USD')->sell->toString()->toBe('1')
        ->losses->forPair('ETH/USD')->total->toString()->toBe('1')
        ->losses->forPair('ETH/USD')->buy->toString()->toBe('0')
        ->losses->forPair('ETH/USD')->sell->toString()->toBe('1')
        ->losses->forBaseCurrency('BTC')->total->toString()->toBe('1')
        ->losses->forBaseCurrency('BTC')->buy->toString()->toBe('0')
        ->losses->forBaseCurrency('BTC')->sell->toString()->toBe('1')
        ->losses->forBaseCurrency('ETH')->total->toString()->toBe('1')
        ->losses->forBaseCurrency('ETH')->buy->toString()->toBe('0')
        ->losses->forBaseCurrency('ETH')->sell->toString()->toBe('1')
        ->losses->forQuoteCurrency('USD')->total->toString()->toBe('1')
        ->losses->forQuoteCurrency('USD')->buy->toString()->toBe('0')
        ->losses->forQuoteCurrency('USD')->sell->toString()->toBe('2')
        ->toArray()->toBe([
            'wins' => [
                'global' => [
                    'total' => '3',
                    'buy' => '4',
                    'sell' => '0',
                ],
                'per_base_currency' => [
                    'BTC' => [
                        'total' => '3',
                        'buy' => '3',
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
                        'total' => '3',
                        'buy' => '4',
                        'sell' => '0',
                    ],
                ],
                'per_pair' => [
                    'BTC/USD' => [
                        'total' => '3',
                        'buy' => '3',
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
                    'total' => '1',
                    'buy' => '0',
                    'sell' => '2',
                ],
                'per_base_currency' => [
                    'ETH' => [
                        'total' => '1',
                        'buy' => '0',
                        'sell' => '1',
                    ],
                    'BTC' => [
                        'total' => '1',
                        'buy' => '0',
                        'sell' => '1',
                    ],
                ],
                'per_quote_currency' => [
                    'USD' => [
                        'total' => '1',
                        'buy' => '0',
                        'sell' => '2',
                    ],
                ],
                'per_pair' => [
                    'ETH/USD' => [
                        'total' => '1',
                        'buy' => '0',
                        'sell' => '1',
                    ],
                    'BTC/USD' => [
                        'total' => '1',
                        'buy' => '0',
                        'sell' => '1',
                    ],
                ],
            ],
        ]);
})->with('streaks-test-trades');
