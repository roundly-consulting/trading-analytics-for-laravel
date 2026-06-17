<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingValue;

it('correctly returns mixed values', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $value = $analytics->value;

    expect($value)
        ->toBeInstanceOf(TradingValue::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->global->total->total->toString()->toBe('82000.0000000000')
        ->global->total->average->toString()->toBe('16400.0000000000')
        ->global->total->highest->toString()->toBe('42000.0000000000')
        ->global->total->lowest->toString()->toBe('1000.0000000000')
        ->global->buy->total->toString()->toBe('76500.0000000000')
        ->global->buy->average->toString()->toBe('25500.0000000000')
        ->global->buy->highest->toString()->toBe('42000.0000000000')
        ->global->buy->lowest->toString()->toBe('4500.0000000000')
        ->global->sell->total->toString()->toBe('5500.0000000000')
        ->global->sell->average->toString()->toBe('2750.0000000000')
        ->global->sell->highest->toString()->toBe('4500.0000000000')
        ->global->sell->lowest->toString()->toBe('1000.0000000000')
        ->forPair('ETH/USD')->total->total->toString()->toBe('76500.0000000000')
        ->forPair('ETH/USD')->total->average->toString()->toBe('25500.0000000000')
        ->forPair('ETH/USD')->total->highest->toString()->toBe('42000.0000000000')
        ->forPair('ETH/USD')->total->lowest->toString()->toBe('4500.0000000000')
        ->forPair('ETH/USD')->buy->total->toString()->toBe('72000.0000000000')
        ->forPair('ETH/USD')->buy->average->toString()->toBe('36000.0000000000')
        ->forPair('ETH/USD')->buy->highest->toString()->toBe('42000.0000000000')
        ->forPair('ETH/USD')->buy->lowest->toString()->toBe('30000.0000000000')
        ->forPair('ETH/USD')->sell->total->toString()->toBe('4500.0000000000')
        ->forPair('ETH/USD')->sell->average->toString()->toBe('4500.0000000000')
        ->forPair('ETH/USD')->sell->highest->toString()->toBe('4500.0000000000')
        ->forPair('ETH/USD')->sell->lowest->toString()->toBe('4500.0000000000')
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '82000.0000000000',
                    'average' => '16400.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '76500.0000000000',
                    'average' => '25500.0000000000',
                    'highest' => [
                        'value' => '42000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '5500.0000000000',
                    'average' => '2750.0000000000',
                    'highest' => [
                        'value' => '4500.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '1000.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '4500.0000000000',
                        'average' => '4500.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '4500.0000000000',
                        'average' => '4500.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
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
                        'total' => '76500.0000000000',
                        'average' => '25500.0000000000',
                        'highest' => [
                            'value' => '42000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '72000.0000000000',
                        'average' => '36000.0000000000',
                        'highest' => [
                            'value' => '42000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '30000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '4500.0000000000',
                        'average' => '4500.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
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
                        'total' => '4500.0000000000',
                        'average' => '4500.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '4500.0000000000',
                        'average' => '4500.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
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
                        'total' => '76500.0000000000',
                        'average' => '25500.0000000000',
                        'highest' => [
                            'value' => '42000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '72000.0000000000',
                        'average' => '36000.0000000000',
                        'highest' => [
                            'value' => '42000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '30000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '4500.0000000000',
                        'average' => '4500.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
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
                        'total' => '82000.0000000000',
                        'average' => '16400.0000000000',
                        'highest' => [
                            'value' => '42000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '1000.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '76500.0000000000',
                        'average' => '25500.0000000000',
                        'highest' => [
                            'value' => '42000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '5500.0000000000',
                        'average' => '2750.0000000000',
                        'highest' => [
                            'value' => '4500.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '1000.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
        ]);
})->with('default-trades');

it('correctly returns highest and lowest pairs', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $value = $analytics->value->toArray();

    expect($value['global']['total'])
        ->toBe([
            'total' => '0.3500000000',
            'average' => '0.1166666666',
            'highest' => [
                'value' => '0.1500000000',
                'pair' => 'SHIB/USD',
            ],
            'lowest' => [
                'value' => '0.1000000000',
                'pair' => 'SHIB/EUR',
            ],
        ]);
})->with('different-highest-and-lowest');
