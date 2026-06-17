<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;

it('correctly returns mixed net profit and loss', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->realizedProfitAndLoss)
        ->toBeInstanceOf(ProfitAndLoss::class)
        ->and($analytics->realizedProfitAndLoss->net)
        ->toBeInstanceOf(NumericDirectionalAggregatesByCurrency::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->global->total->total->toString()->toBe('-1372.0000000000')
        ->global->total->average->toString()->toBe('-274.4000000000')
        ->global->total->highest->toString()->toBe('1018.0000000000')
        ->global->total->lowest->toString()->toBe('-1963.0000000000')
        ->global->buy->total->toString()->toBe('-910.0000000000')
        ->global->buy->average->toString()->toBe('-303.3333333333')
        ->global->buy->highest->toString()->toBe('1018.0000000000')
        ->global->buy->lowest->toString()->toBe('-1963.0000000000')
        ->global->sell->total->toString()->toBe('-462.0000000000')
        ->global->sell->average->toString()->toBe('-231.0000000000')
        ->global->sell->highest->toString()->toBe('-152.0000000000')
        ->global->sell->lowest->toString()->toBe('-310.0000000000')
        ->forPair('ETH/USD')->total->total->toString()->toBe('-1255.0000000000')
        ->forPair('ETH/USD')->total->average->toString()->toBe('-418.3333333333')
        ->forPair('ETH/USD')->total->highest->toString()->toBe('1018.0000000000')
        ->forPair('ETH/USD')->total->lowest->toString()->toBe('-1963.0000000000')
        ->forPair('ETH/USD')->buy->total->toString()->toBe('-945.0000000000')
        ->forPair('ETH/USD')->buy->average->toString()->toBe('-472.5000000000')
        ->forPair('ETH/USD')->buy->highest->toString()->toBe('1018.0000000000')
        ->forPair('ETH/USD')->buy->lowest->toString()->toBe('-1963.0000000000')
        ->forPair('ETH/USD')->sell->total->toString()->toBe('-310.0000000000')
        ->forPair('ETH/USD')->sell->average->toString()->toBe('-310.0000000000')
        ->forPair('ETH/USD')->sell->highest->toString()->toBe('-310.0000000000')
        ->forPair('ETH/USD')->sell->lowest->toString()->toBe('-310.0000000000')
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '-1372.0000000000',
                    'average' => '-274.4000000000',
                    'highest' => [
                        'value' => '1018.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '-1963.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'buy' => [
                    'total' => '-910.0000000000',
                    'average' => '-303.3333333333',
                    'highest' => [
                        'value' => '1018.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '-1963.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
                'sell' => [
                    'total' => '-462.0000000000',
                    'average' => '-231.0000000000',
                    'highest' => [
                        'value' => '-152.0000000000',
                        'pair' => 'XRP/USD',
                    ],
                    'lowest' => [
                        'value' => '-310.0000000000',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '35.0000000000',
                        'average' => '35.0000000000',
                        'highest' => [
                            'value' => '35.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '35.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '35.0000000000',
                        'average' => '35.0000000000',
                        'highest' => [
                            'value' => '35.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '35.0000000000',
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
                        'total' => '-1255.0000000000',
                        'average' => '-418.3333333333',
                        'highest' => [
                            'value' => '1018.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1963.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '-945.0000000000',
                        'average' => '-472.5000000000',
                        'highest' => [
                            'value' => '1018.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1963.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-310.0000000000',
                        'average' => '-310.0000000000',
                        'highest' => [
                            'value' => '-310.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-310.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP/USD' => [
                    'total' => [
                        'total' => '-152.0000000000',
                        'average' => '-152.0000000000',
                        'highest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-152.0000000000',
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
                        'total' => '-152.0000000000',
                        'average' => '-152.0000000000',
                        'highest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '35.0000000000',
                        'average' => '35.0000000000',
                        'highest' => [
                            'value' => '35.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '35.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '35.0000000000',
                        'average' => '35.0000000000',
                        'highest' => [
                            'value' => '35.0000000000',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '35.0000000000',
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
                        'total' => '-1255.0000000000',
                        'average' => '-418.3333333333',
                        'highest' => [
                            'value' => '1018.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1963.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '-945.0000000000',
                        'average' => '-472.5000000000',
                        'highest' => [
                            'value' => '1018.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1963.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-310.0000000000',
                        'average' => '-310.0000000000',
                        'highest' => [
                            'value' => '-310.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-310.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
                'XRP' => [
                    'total' => [
                        'total' => '-152.0000000000',
                        'average' => '-152.0000000000',
                        'highest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-152.0000000000',
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
                        'total' => '-152.0000000000',
                        'average' => '-152.0000000000',
                        'highest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '-1372.0000000000',
                        'average' => '-274.4000000000',
                        'highest' => [
                            'value' => '1018.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1963.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '-910.0000000000',
                        'average' => '-303.3333333333',
                        'highest' => [
                            'value' => '1018.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '-1963.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '-462.0000000000',
                        'average' => '-231.0000000000',
                        'highest' => [
                            'value' => '-152.0000000000',
                            'pair' => 'XRP/USD',
                        ],
                        'lowest' => [
                            'value' => '-310.0000000000',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
            ],
        ]);
})->with('default-trades');

it('correctly returns highest and lowest pairs for net profit and loss', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    $value = $analytics->realizedProfitAndLoss->net->toArray();

    expect($value['global']['total'])
        ->toBe([
            'total' => '-31.3320000000',
            'average' => '-10.4440000000',
            'highest' => [
                'value' => '-7.9770000000',
                'pair' => 'SHIB/EUR',
            ],
            'lowest' => [
                'value' => '-14.8350000000',
                'pair' => 'SHIB/USD',
            ],
        ]);
})->with('different-highest-and-lowest');
