<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingCommissions;

it('correctly returns mixed commissions', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $commission = $analytics->commission;

    expect($commission)
        ->toBeInstanceOf(TradingCommissions::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->global->total->total->toString()->toBe('2132.0000000000')
        ->global->total->average->toString()->toBe('426.4000000000')
        ->global->total->highest->toString()->toBe('1123.0000000000')
        ->global->total->lowest->toString()->toBe('2.0000000000')
        ->global->buy->total->toString()->toBe('2120.0000000000')
        ->global->buy->average->toString()->toBe('706.6666666666')
        ->global->buy->highest->toString()->toBe('1123.0000000000')
        ->global->buy->lowest->toString()->toBe('15.0000000000')
        ->global->sell->total->toString()->toBe('12.0000000000')
        ->global->sell->average->toString()->toBe('6.0000000000')
        ->global->sell->highest->toString()->toBe('10.0000000000')
        ->global->sell->lowest->toString()->toBe('2.0000000000')
        ->forPair('ETH/USD')->total->total->toString()->toBe('2115.0000000000')
        ->forPair('ETH/USD')->total->average->toString()->toBe('705.0000000000')
        ->forPair('ETH/USD')->total->highest->toString()->toBe('1123.0000000000')
        ->forPair('ETH/USD')->total->lowest->toString()->toBe('10.0000000000')
        ->forPair('ETH/USD')->buy->total->toString()->toBe('2105.0000000000')
        ->forPair('ETH/USD')->buy->average->toString()->toBe('1052.5000000000')
        ->forPair('ETH/USD')->buy->highest->toString()->toBe('1123.0000000000')
        ->forPair('ETH/USD')->buy->lowest->toString()->toBe('982.0000000000')
        ->forPair('ETH/USD')->sell->total->toString()->toBe('10.0000000000')
        ->forPair('ETH/USD')->sell->average->toString()->toBe('10.0000000000')
        ->forPair('ETH/USD')->sell->highest->toString()->toBe('10.0000000000')
        ->forPair('ETH/USD')->sell->lowest->toString()->toBe('10.0000000000')
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '2132.0000000000',
                    'average' => '426.4000000000',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
                'buy' => [
                    'total' => '2120.0000000000',
                    'average' => '706.6666666666',
                    'highest' => [
                        'value' => '1123.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '15.0000000000',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '12.0000000000',
                    'average' => '6.0000000000',
                    'highest' => [
                        'value' => '10.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '2.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '15.0000000000',
                        'average' => '15.0000000000',
                        'highest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '15.0000000000',
                        'average' => '15.0000000000',
                        'highest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '15.0000000000',
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
                        'total' => '2115.0000000000',
                        'average' => '705.0000000000',
                        'highest' => [
                            'value' => '1123.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '2105.0000000000',
                        'average' => '1052.5000000000',
                        'highest' => [
                            'value' => '1123.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '982.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '10.0000000000',
                        'average' => '10.0000000000',
                        'highest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP/USD' => [
                    'total' => [
                        'total' => '2.0000000000',
                        'average' => '2.0000000000',
                        'highest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '2.0000000000',
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
                        'total' => '2.0000000000',
                        'average' => '2.0000000000',
                        'highest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '15.0000000000',
                        'average' => '15.0000000000',
                        'highest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '15.0000000000',
                        'average' => '15.0000000000',
                        'highest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '15.0000000000',
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
                        'total' => '2115.0000000000',
                        'average' => '705.0000000000',
                        'highest' => [
                            'value' => '1123.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '2105.0000000000',
                        'average' => '1052.5000000000',
                        'highest' => [
                            'value' => '1123.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '982.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '10.0000000000',
                        'average' => '10.0000000000',
                        'highest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP' => [
                    'total' => [
                        'total' => '2.0000000000',
                        'average' => '2.0000000000',
                        'highest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '2.0000000000',
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
                        'total' => '2.0000000000',
                        'average' => '2.0000000000',
                        'highest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '2132.0000000000',
                        'average' => '426.4000000000',
                        'highest' => [
                            'value' => '1123.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '2.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '2120.0000000000',
                        'average' => '706.6666666666',
                        'highest' => [
                            'value' => '1123.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '15.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '12.0000000000',
                        'average' => '6.0000000000',
                        'highest' => [
                            'value' => '10.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '2.0000000000',
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

    $commission = $analytics->commission->toArray();

    expect($commission['global']['total'])
        ->toBe([
            'total' => '31.5000000000',
            'average' => '10.5000000000',
            'highest' => [
                'value' => '15.0000000000',
                'pair' => 'SHIB/USD',
            ],
            'lowest' => [
                'value' => '8.0000000000',
                'pair' => 'SHIB/EUR',
            ],
        ]);
})->with('different-highest-and-lowest');

// The open SHIB/USD trade carries no commission: it paid none, so it counts as a zero —
// the per-trade average was already taken over it; now its 0 is the lowest as well.
it('counts a trade without a commission as a zero commission', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->commission->toArray())
        ->toBe([
            'global' => [
                'total' => [
                    'total' => '16.5000000000',
                    'average' => '5.5000000000',
                    'highest' => [
                        'value' => '8.5000000000',
                        'pair' => 'SHIB/EUR',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => 'SHIB/USD',
                    ],
                ],
                'buy' => [
                    'total' => '8.5000000000',
                    'average' => '4.2500000000',
                    'highest' => [
                        'value' => '8.5000000000',
                        'pair' => 'SHIB/EUR',
                    ],
                    'lowest' => [
                        'value' => '0.0000000000',
                        'pair' => 'SHIB/USD',
                    ],
                ],
                'sell' => [
                    'total' => '8.0000000000',
                    'average' => '8.0000000000',
                    'highest' => [
                        'value' => '8.0000000000',
                        'pair' => 'SHIB/EUR',
                    ],
                    'lowest' => [
                        'value' => '8.0000000000',
                        'pair' => 'SHIB/EUR',
                    ],
                ],
            ],
            'per_pair' => [
                'SHIB/USD' => [
                    'total' => [
                        'total' => '0.0000000000',
                        'average' => '0.0000000000',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.0000000000',
                        'average' => '0.0000000000',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
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
                'SHIB/EUR' => [
                    'total' => [
                        'total' => '16.5000000000',
                        'average' => '8.2500000000',
                        'highest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                    'buy' => [
                        'total' => '8.5000000000',
                        'average' => '8.5000000000',
                        'highest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                    'sell' => [
                        'total' => '8.0000000000',
                        'average' => '8.0000000000',
                        'highest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'SHIB' => [
                    'total' => [
                        'total' => '16.5000000000',
                        'average' => '5.5000000000',
                        'highest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '8.5000000000',
                        'average' => '4.2500000000',
                        'highest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '8.0000000000',
                        'average' => '8.0000000000',
                        'highest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '0.0000000000',
                        'average' => '0.0000000000',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.0000000000',
                        'average' => '0.0000000000',
                        'highest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.0000000000',
                            'pair' => 'SHIB/USD',
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
                'EUR' => [
                    'total' => [
                        'total' => '16.5000000000',
                        'average' => '8.2500000000',
                        'highest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                    'buy' => [
                        'total' => '8.5000000000',
                        'average' => '8.5000000000',
                        'highest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.5000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                    'sell' => [
                        'total' => '8.0000000000',
                        'average' => '8.0000000000',
                        'highest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '8.0000000000',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                ],
            ],
        ]);
})->with('open-and-closed-trades');
