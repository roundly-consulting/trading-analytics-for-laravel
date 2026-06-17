<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingVolume;

it('correctly returns mixed volumes', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $volume = $analytics->volume;

    expect($volume)
        ->toBeInstanceOf(TradingVolume::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->global->total->total->toString()->toBe('1023.6000000000')
        ->global->total->average->toString()->toBe('204.7200000000')
        ->global->total->highest->toString()->toBe('1000.0000000000')
        ->global->total->lowest->toString()->toBe('0.1000000000')
        ->global->buy->total->toString()->toBe('22.1000000000')
        ->global->buy->average->toString()->toBe('7.3666666666')
        ->global->buy->highest->toString()->toBe('12.0000000000')
        ->global->buy->lowest->toString()->toBe('0.1000000000')
        ->global->sell->total->toString()->toBe('1001.5000000000')
        ->global->sell->average->toString()->toBe('500.7500000000')
        ->global->sell->highest->toString()->toBe('1000.0000000000')
        ->global->sell->lowest->toString()->toBe('1.5000000000')
        ->forPair('ETH/USD')->total->total->toString()->toBe('23.5000000000')
        ->forPair('ETH/USD')->total->average->toString()->toBe('7.8333333333')
        ->forPair('ETH/USD')->total->highest->toString()->toBe('12.0000000000')
        ->forPair('ETH/USD')->total->lowest->toString()->toBe('1.5000000000')
        ->forPair('ETH/USD')->buy->total->toString()->toBe('22.0000000000')
        ->forPair('ETH/USD')->buy->average->toString()->toBe('11.0000000000')
        ->forPair('ETH/USD')->buy->highest->toString()->toBe('12.0000000000')
        ->forPair('ETH/USD')->buy->lowest->toString()->toBe('10.0000000000')
        ->forPair('ETH/USD')->sell->total->toString()->toBe('1.5000000000')
        ->forPair('ETH/USD')->sell->average->toString()->toBe('1.5000000000')
        ->forPair('ETH/USD')->sell->highest->toString()->toBe('1.5000000000')
        ->forPair('ETH/USD')->sell->lowest->toString()->toBe('1.5000000000')
        ->toArray()->toBe([
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
        ]);
})->with('default-trades');

it('correctly returns btc trade volumes', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $volume = $analytics->volume;

    expect($volume)
        ->toBeInstanceOf(TradingVolume::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->global->total->total->toString()->toBe('1.3001243210')
        ->global->total->average->toString()->toBe('0.4333747736')
        ->global->total->highest->toString()->toBe('1.2000000000')
        ->global->total->lowest->toString()->toBe('0.0001243210')
        ->global->buy->total->toString()->toBe('1.3001243210')
        ->global->buy->average->toString()->toBe('0.4333747736')
        ->global->buy->highest->toString()->toBe('1.2000000000')
        ->global->buy->lowest->toString()->toBe('0.0001243210')
        ->global->sell->total->toString()->toBe('0.0000000000')
        ->global->sell->average->toString()->toBe('0.0000000000')
        ->global->sell->highest->toString()->toBe('0.0000000000')
        ->global->sell->lowest->toString()->toBe('0.0000000000')
        ->forPair('BTC/USD')->total->total->toString()->toBe('1.3001243210')
        ->forPair('BTC/USD')->total->average->toString()->toBe('0.4333747736')
        ->forPair('BTC/USD')->total->highest->toString()->toBe('1.2000000000')
        ->forPair('BTC/USD')->total->lowest->toString()->toBe('0.0001243210')
        ->forPair('BTC/USD')->buy->total->toString()->toBe('1.3001243210')
        ->forPair('BTC/USD')->buy->average->toString()->toBe('0.4333747736')
        ->forPair('BTC/USD')->buy->highest->toString()->toBe('1.2000000000')
        ->forPair('BTC/USD')->buy->lowest->toString()->toBe('0.0001243210')
        ->forPair('BTC/USD')->sell->total->toString()->toBe('0.0000000000')
        ->forPair('BTC/USD')->sell->average->toString()->toBe('0.0000000000')
        ->forPair('BTC/USD')->sell->highest->toString()->toBe('0.0000000000')
        ->forPair('BTC/USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '1.3001243210',
                    'average' => '0.4333747736',
                    'highest' => [
                        'value' => '1.2000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '0.0001243210',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '1.3001243210',
                    'average' => '0.4333747736',
                    'highest' => [
                        'value' => '1.2000000000',
                        'pair' => 'BTC/USD',
                    ],
                    'lowest' => [
                        'value' => '0.0001243210',
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
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '1.3001243210',
                        'average' => '0.4333747736',
                        'highest' => [
                            'value' => '1.2000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0001243210',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1.3001243210',
                        'average' => '0.4333747736',
                        'highest' => [
                            'value' => '1.2000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0001243210',
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
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '1.3001243210',
                        'average' => '0.4333747736',
                        'highest' => [
                            'value' => '1.2000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0001243210',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1.3001243210',
                        'average' => '0.4333747736',
                        'highest' => [
                            'value' => '1.2000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0001243210',
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
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '1.3001243210',
                        'average' => '0.4333747736',
                        'highest' => [
                            'value' => '1.2000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0001243210',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1.3001243210',
                        'average' => '0.4333747736',
                        'highest' => [
                            'value' => '1.2000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0001243210',
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
            ],
        ]);
})->with('btc-buys');

it('correctly returns highest and lowest pairs', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $volume = $analytics->volume->toArray();

    expect($volume['global']['total'])
        ->toBe([
            'total' => '35000.0000000000',
            'average' => '11666.6666666666',
            'highest' => [
                'value' => '15000.0000000000',
                'pair' => 'SHIB/USD',
            ],
            'lowest' => [
                'value' => '10000.0000000000',
                'pair' => 'SHIB/EUR',
            ],
        ]);
})->with('different-highest-and-lowest');
