<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;

it('correctly returns mixed gross profit and loss', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->realizedProfitAndLoss)
        ->toBeInstanceOf(ProfitAndLoss::class)
        ->and($analytics->realizedProfitAndLoss->gross)
        ->toBeInstanceOf(NumericDirectionalAggregatesByCurrency::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->global->total->total->toString()->toBe('760.0000000000')
        ->global->total->average->toString()->toBe('152.0000000000')
        ->global->total->highest->toString()->toBe('2000.0000000000')
        ->global->total->lowest->toString()->toBe('-840.0000000000')
        ->global->buy->total->toString()->toBe('1210.0000000000')
        ->global->buy->average->toString()->toBe('403.3333333333')
        ->global->buy->highest->toString()->toBe('2000.0000000000')
        ->global->buy->lowest->toString()->toBe('-840.0000000000')
        ->global->sell->total->toString()->toBe('-450.0000000000')
        ->global->sell->average->toString()->toBe('-225.0000000000')
        ->global->sell->highest->toString()->toBe('-150.0000000000')
        ->global->sell->lowest->toString()->toBe('-300.0000000000')
        ->forPair('ETH/USD')->total->total->toString()->toBe('860.0000000000')
        ->forPair('ETH/USD')->total->average->toString()->toBe('286.6666666666')
        ->forPair('ETH/USD')->total->highest->toString()->toBe('2000.0000000000')
        ->forPair('ETH/USD')->total->lowest->toString()->toBe('-840.0000000000')
        ->forPair('ETH/USD')->buy->total->toString()->toBe('1160.0000000000')
        ->forPair('ETH/USD')->buy->average->toString()->toBe('580.0000000000')
        ->forPair('ETH/USD')->buy->highest->toString()->toBe('2000.0000000000')
        ->forPair('ETH/USD')->buy->lowest->toString()->toBe('-840.0000000000')
        ->forPair('ETH/USD')->sell->total->toString()->toBe('-300.0000000000')
        ->forPair('ETH/USD')->sell->average->toString()->toBe('-300.0000000000')
        ->forPair('ETH/USD')->sell->highest->toString()->toBe('-300.0000000000')
        ->forPair('ETH/USD')->sell->lowest->toString()->toBe('-300.0000000000')
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '760.0000000000',
                    'average' => '152.0000000000',
                    'highest' => [
                        'value' => '2000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '-840.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '1210.0000000000',
                    'average' => '403.3333333333',
                    'highest' => [
                        'value' => '2000.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '-840.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '-450.0000000000',
                    'average' => '-225.0000000000',
                    'highest' => [
                        'value' => '-150.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '-300.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '50.0000000000',
                        'average' => '50.0000000000',
                        'highest' => [
                            'value' => '50.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '50.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '50.0000000000',
                        'average' => '50.0000000000',
                        'highest' => [
                            'value' => '50.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '50.0000000000',
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
                        'total' => '860.0000000000',
                        'average' => '286.6666666666',
                        'highest' => [
                            'value' => '2000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-840.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1160.0000000000',
                        'average' => '580.0000000000',
                        'highest' => [
                            'value' => '2000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-840.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-300.0000000000',
                        'average' => '-300.0000000000',
                        'highest' => [
                            'value' => '-300.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-300.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP/USD' => [
                    'total' => [
                        'total' => '-150.0000000000',
                        'average' => '-150.0000000000',
                        'highest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-150.0000000000',
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
                        'total' => '-150.0000000000',
                        'average' => '-150.0000000000',
                        'highest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '50.0000000000',
                        'average' => '50.0000000000',
                        'highest' => [
                            'value' => '50.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '50.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '50.0000000000',
                        'average' => '50.0000000000',
                        'highest' => [
                            'value' => '50.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '50.0000000000',
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
                        'total' => '860.0000000000',
                        'average' => '286.6666666666',
                        'highest' => [
                            'value' => '2000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-840.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1160.0000000000',
                        'average' => '580.0000000000',
                        'highest' => [
                            'value' => '2000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-840.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-300.0000000000',
                        'average' => '-300.0000000000',
                        'highest' => [
                            'value' => '-300.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-300.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP' => [
                    'total' => [
                        'total' => '-150.0000000000',
                        'average' => '-150.0000000000',
                        'highest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-150.0000000000',
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
                        'total' => '-150.0000000000',
                        'average' => '-150.0000000000',
                        'highest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '760.0000000000',
                        'average' => '152.0000000000',
                        'highest' => [
                            'value' => '2000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-840.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1210.0000000000',
                        'average' => '403.3333333333',
                        'highest' => [
                            'value' => '2000.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-840.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-450.0000000000',
                        'average' => '-225.0000000000',
                        'highest' => [
                            'value' => '-150.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-300.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
            ],
        ]);
})->with('default-trades');

it('correctly returns highest and lowest pairs for gross profit and loss', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $value = $analytics->realizedProfitAndLoss->gross->toArray();

    expect($value['global']['total'])
        ->toBe([
            'total' => '0.1680000000',
            'average' => '0.0560000000',
            'highest' => [
                'value' => '0.1650000000',
                'pair' => 'SHIB/USD',
            ],
            'lowest' => [
                'value' => '-0.0200000000',
                'pair' => 'SHIB/EUR',
            ],
        ]);
})->with('different-highest-and-lowest');
