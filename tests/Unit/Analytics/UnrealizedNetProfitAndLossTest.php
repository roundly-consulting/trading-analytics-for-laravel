<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;

it('correctly returns unrealized net profit and loss', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->unrealizedProfitAndLoss)
        ->toBeInstanceOf(ProfitAndLoss::class)
        ->and($analytics->unrealizedProfitAndLoss->net)
        ->toBeInstanceOf(NumericDirectionalAggregatesByCurrency::class)
        ->global->toBeInstanceOf(NumericDirectionalAggregates::class)
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '0.1650000000',
                    'average' => '0.1650000000',
                    'highest' => [
                        'value' => '0.1650000000',
                        'pair' => 'SHIB/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1650000000',
                        'pair' => 'SHIB/USD',
                    ],
                ],
                'buy' => [
                    'total' => '0.1650000000',
                    'average' => '0.1650000000',
                    'highest' => [
                        'value' => '0.1650000000',
                        'pair' => 'SHIB/USD',
                    ],
                    'lowest' => [
                        'value' => '0.1650000000',
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
            'per_pair' => [
                'SHIB/USD' => [
                    'total' => [
                        'total' => '0.1650000000',
                        'average' => '0.1650000000',
                        'highest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.1650000000',
                        'average' => '0.1650000000',
                        'highest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.1650000000',
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
            ],
            'per_base_currency' => [
                'SHIB' => [
                    'total' => [
                        'total' => '0.1650000000',
                        'average' => '0.1650000000',
                        'highest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.1650000000',
                        'average' => '0.1650000000',
                        'highest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.1650000000',
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
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '0.1650000000',
                        'average' => '0.1650000000',
                        'highest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '0.1650000000',
                        'average' => '0.1650000000',
                        'highest' => [
                            'value' => '0.1650000000',
                            'pair' => 'SHIB/USD',
                        ],
                        'lowest' => [
                            'value' => '0.1650000000',
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
            ],
        ]);
})->with('open-and-closed-trades');
