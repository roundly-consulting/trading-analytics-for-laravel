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
                            'average' => '0.0550000000',
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
                            'average' => '0.0825000000',
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
                                'average' => '0.0550000000',
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
                                'average' => '0.0825000000',
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
                'losses' => [
                    'total' => '-0.0430000000',
                    'per_pair' => [
                        'SHIB/EUR' => '-0.0430000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '-0.0430000000',
                    ],
                    'per_quote_currency' => [
                        'EUR' => '-0.0430000000',
                    ],
                ],
            ],
            'net' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '0.1650000000',
                            'average' => '0.0550000000',
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
                            'average' => '0.0825000000',
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
                                'average' => '0.0550000000',
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
                                'average' => '0.0825000000',
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
                'losses' => [
                    'total' => '-16.5430000000',
                    'per_pair' => [
                        'SHIB/EUR' => '-16.5430000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '-16.5430000000',
                    ],
                    'per_quote_currency' => [
                        'EUR' => '-16.5430000000',
                    ],
                ],
            ],
        ]);
})->with('open-and-closed-trades');
