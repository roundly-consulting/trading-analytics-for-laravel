<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;

it('correctly returns unrealized profits and losses', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->unrealizedProfitAndLoss)
        ->toBeInstanceOf(ProfitAndLoss::class)
        ->toArray()->toBe([
            'gross' => [
                'pnl' => [
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
                ],
                'profits' => [
                    'total' => '0.1650000000',
                    'per_pair' => [
                        'SHIB/USD' => '0.1650000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '0.1650000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '0.1650000000',
                    ],
                ],
                // The only open trade is a winner; the realized SHIB/EUR losses stay out.
                'losses' => [
                    'total' => '0.0000000000',
                    'per_pair' => [],
                    'per_base_currency' => [],
                    'per_quote_currency' => [],
                ],
            ],
            'net' => [
                'pnl' => [
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
                ],
                'profits' => [
                    'total' => '0.1650000000',
                    'per_pair' => [
                        'SHIB/USD' => '0.1650000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '0.1650000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '0.1650000000',
                    ],
                ],
                // The only open trade is a winner; the realized SHIB/EUR losses stay out.
                'losses' => [
                    'total' => '0.0000000000',
                    'per_pair' => [],
                    'per_base_currency' => [],
                    'per_quote_currency' => [],
                ],
            ],
        ]);
})->with('open-and-closed-trades');
