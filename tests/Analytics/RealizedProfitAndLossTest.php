<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\ProfitAndLoss;

it('correctly returns profits and losses using exact returns', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->realizedProfitAndLoss)
        ->toBeInstanceOf(ProfitAndLoss::class)
        ->gross->global->total->total->toString()->toBe('13500.0000000000')
        ->gross->global->total->average->toString()->toBe('4500.0000000000')
        ->gross->global->total->highest->toString()->toBe('6000.0000000000')
        ->gross->global->total->lowest->toString()->toBe('3000.0000000000')
        ->gross->global->buy->total->toString()->toBe('9000.0000000000')
        ->gross->global->buy->average->toString()->toBe('4500.0000000000')
        ->gross->global->buy->highest->toString()->toBe('6000.0000000000')
        ->gross->global->buy->lowest->toString()->toBe('3000.0000000000')
        ->gross->global->sell->total->toString()->toBe('4500.0000000000')
        ->gross->global->sell->average->toString()->toBe('4500.0000000000')
        ->gross->global->sell->highest->toString()->toBe('4500.0000000000')
        ->gross->global->sell->lowest->toString()->toBe('4500.0000000000')
        ->gross->forPair('SHIB/USD')->total->total->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->total->average->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->total->highest->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->total->lowest->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->buy->total->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->buy->average->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->buy->highest->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->buy->lowest->toString()->toBe('6000.0000000000')
        ->gross->forPair('SHIB/USD')->sell->total->toString()->toBe('0.0000000000')
        ->gross->forPair('SHIB/USD')->sell->average->toString()->toBe('0.0000000000')
        ->gross->forPair('SHIB/USD')->sell->highest->toString()->toBe('0.0000000000')
        ->gross->forPair('SHIB/USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->gross->forPair('SHIB/EUR')->total->total->toString()->toBe('7500.0000000000')
        ->gross->forPair('SHIB/EUR')->total->average->toString()->toBe('3750.0000000000')
        ->gross->forPair('SHIB/EUR')->total->highest->toString()->toBe('4500.0000000000')
        ->gross->forPair('SHIB/EUR')->total->lowest->toString()->toBe('3000.0000000000')
        ->gross->forPair('SHIB/EUR')->buy->total->toString()->toBe('3000.0000000000')
        ->gross->forPair('SHIB/EUR')->buy->average->toString()->toBe('3000.0000000000')
        ->gross->forPair('SHIB/EUR')->buy->highest->toString()->toBe('3000.0000000000')
        ->gross->forPair('SHIB/EUR')->buy->lowest->toString()->toBe('3000.0000000000')
        ->gross->forPair('SHIB/EUR')->sell->total->toString()->toBe('4500.0000000000')
        ->gross->forPair('SHIB/EUR')->sell->average->toString()->toBe('4500.0000000000')
        ->gross->forPair('SHIB/EUR')->sell->highest->toString()->toBe('4500.0000000000')
        ->gross->forPair('SHIB/EUR')->sell->lowest->toString()->toBe('4500.0000000000')
        ->gross->forBaseCurrency('SHIB')->total->total->toString()->toBe('13500.0000000000')
        ->gross->forBaseCurrency('SHIB')->total->average->toString()->toBe('4500.0000000000')
        ->gross->forBaseCurrency('SHIB')->total->highest->toString()->toBe('6000.0000000000')
        ->gross->forBaseCurrency('SHIB')->total->lowest->toString()->toBe('3000.0000000000')
        ->gross->forBaseCurrency('SHIB')->buy->total->toString()->toBe('9000.0000000000')
        ->gross->forBaseCurrency('SHIB')->buy->average->toString()->toBe('4500.0000000000')
        ->gross->forBaseCurrency('SHIB')->buy->highest->toString()->toBe('6000.0000000000')
        ->gross->forBaseCurrency('SHIB')->buy->lowest->toString()->toBe('3000.0000000000')
        ->gross->forBaseCurrency('SHIB')->sell->total->toString()->toBe('4500.0000000000')
        ->gross->forBaseCurrency('SHIB')->sell->average->toString()->toBe('4500.0000000000')
        ->gross->forBaseCurrency('SHIB')->sell->highest->toString()->toBe('4500.0000000000')
        ->gross->forBaseCurrency('SHIB')->sell->lowest->toString()->toBe('4500.0000000000')
        ->gross->forQuoteCurrency('USD')->total->total->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->total->average->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->total->highest->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->total->lowest->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->buy->total->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->buy->average->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->buy->highest->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->buy->lowest->toString()->toBe('6000.0000000000')
        ->gross->forQuoteCurrency('USD')->sell->total->toString()->toBe('0.0000000000')
        ->gross->forQuoteCurrency('USD')->sell->average->toString()->toBe('0.0000000000')
        ->gross->forQuoteCurrency('USD')->sell->highest->toString()->toBe('0.0000000000')
        ->gross->forQuoteCurrency('USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->gross->forQuoteCurrency('EUR')->total->total->toString()->toBe('7500.0000000000')
        ->gross->forQuoteCurrency('EUR')->total->average->toString()->toBe('3750.0000000000')
        ->gross->forQuoteCurrency('EUR')->total->highest->toString()->toBe('4500.0000000000')
        ->gross->forQuoteCurrency('EUR')->total->lowest->toString()->toBe('3000.0000000000')
        ->gross->forQuoteCurrency('EUR')->buy->total->toString()->toBe('3000.0000000000')
        ->gross->forQuoteCurrency('EUR')->buy->average->toString()->toBe('3000.0000000000')
        ->gross->forQuoteCurrency('EUR')->buy->highest->toString()->toBe('3000.0000000000')
        ->gross->forQuoteCurrency('EUR')->buy->lowest->toString()->toBe('3000.0000000000')
        ->gross->forQuoteCurrency('EUR')->sell->total->toString()->toBe('4500.0000000000')
        ->gross->forQuoteCurrency('EUR')->sell->average->toString()->toBe('4500.0000000000')
        ->gross->forQuoteCurrency('EUR')->sell->highest->toString()->toBe('4500.0000000000')
        ->gross->forQuoteCurrency('EUR')->sell->lowest->toString()->toBe('4500.0000000000')
        ->grossProfits->total->toString()->toBe('13500.0000000000')
        ->grossProfits->forPair('SHIB/USD')->toString()->toBe('6000.0000000000')
        ->grossProfits->forPair('SHIB/EUR')->toString()->toBe('7500.0000000000')
        ->grossProfits->forBaseCurrency('SHIB')->toString()->toBe('13500.0000000000')
        ->grossProfits->forQuoteCurrency('USD')->toString()->toBe('6000.0000000000')
        ->grossProfits->forQuoteCurrency('EUR')->toString()->toBe('7500.0000000000')
        ->grossLosses->total->toString()->toBe('0.0000000000')
        ->grossLosses->forPair('SHIB/USD')->isZero()->toBeTrue()
        ->grossLosses->forPair('SHIB/EUR')->isZero()->toBeTrue()
        ->grossLosses->forBaseCurrency('SHIB')->isZero()->toBeTrue()
        ->grossLosses->forQuoteCurrency('USD')->isZero()->toBeTrue()
        ->grossLosses->forQuoteCurrency('EUR')->isZero()->toBeTrue()
        ->net->global->total->total->toString()->toBe('12750.9920000000')
        ->net->global->total->average->toString()->toBe('4250.3306666666')
        ->net->global->total->highest->toString()->toBe('5749.9950000000')
        ->net->global->total->lowest->toString()->toBe('2814.9980000000')
        ->net->global->buy->total->toString()->toBe('8564.9930000000')
        ->net->global->buy->average->toString()->toBe('4282.4965000000')
        ->net->global->buy->highest->toString()->toBe('5749.9950000000')
        ->net->global->buy->lowest->toString()->toBe('2814.9980000000')
        ->net->global->sell->total->toString()->toBe('4185.9990000000')
        ->net->global->sell->average->toString()->toBe('4185.9990000000')
        ->net->global->sell->highest->toString()->toBe('4185.9990000000')
        ->net->global->sell->lowest->toString()->toBe('4185.9990000000')
        ->net->forPair('SHIB/USD')->total->total->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->total->average->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->total->highest->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->total->lowest->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->buy->total->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->buy->average->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->buy->highest->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->buy->lowest->toString()->toBe('5749.9950000000')
        ->net->forPair('SHIB/USD')->sell->total->toString()->toBe('0.0000000000')
        ->net->forPair('SHIB/USD')->sell->average->toString()->toBe('0.0000000000')
        ->net->forPair('SHIB/USD')->sell->highest->toString()->toBe('0.0000000000')
        ->net->forPair('SHIB/USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->net->forPair('SHIB/EUR')->total->total->toString()->toBe('7000.9970000000')
        ->net->forPair('SHIB/EUR')->total->average->toString()->toBe('3500.4985000000')
        ->net->forPair('SHIB/EUR')->total->highest->toString()->toBe('4185.9990000000')
        ->net->forPair('SHIB/EUR')->total->lowest->toString()->toBe('2814.9980000000')
        ->net->forPair('SHIB/EUR')->buy->total->toString()->toBe('2814.9980000000')
        ->net->forPair('SHIB/EUR')->buy->average->toString()->toBe('2814.9980000000')
        ->net->forPair('SHIB/EUR')->buy->highest->toString()->toBe('2814.9980000000')
        ->net->forPair('SHIB/EUR')->buy->lowest->toString()->toBe('2814.9980000000')
        ->net->forPair('SHIB/EUR')->sell->total->toString()->toBe('4185.9990000000')
        ->net->forPair('SHIB/EUR')->sell->average->toString()->toBe('4185.9990000000')
        ->net->forPair('SHIB/EUR')->sell->highest->toString()->toBe('4185.9990000000')
        ->net->forPair('SHIB/EUR')->sell->lowest->toString()->toBe('4185.9990000000')
        ->net->forBaseCurrency('SHIB')->total->total->toString()->toBe('12750.9920000000')
        ->net->forBaseCurrency('SHIB')->total->average->toString()->toBe('4250.3306666666')
        ->net->forBaseCurrency('SHIB')->total->highest->toString()->toBe('5749.9950000000')
        ->net->forBaseCurrency('SHIB')->total->lowest->toString()->toBe('2814.9980000000')
        ->net->forBaseCurrency('SHIB')->buy->total->toString()->toBe('8564.9930000000')
        ->net->forBaseCurrency('SHIB')->buy->average->toString()->toBe('4282.4965000000')
        ->net->forBaseCurrency('SHIB')->buy->highest->toString()->toBe('5749.9950000000')
        ->net->forBaseCurrency('SHIB')->buy->lowest->toString()->toBe('2814.9980000000')
        ->net->forBaseCurrency('SHIB')->sell->total->toString()->toBe('4185.9990000000')
        ->net->forBaseCurrency('SHIB')->sell->average->toString()->toBe('4185.9990000000')
        ->net->forBaseCurrency('SHIB')->sell->highest->toString()->toBe('4185.9990000000')
        ->net->forBaseCurrency('SHIB')->sell->lowest->toString()->toBe('4185.9990000000')
        ->net->forQuoteCurrency('USD')->total->total->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->total->average->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->total->highest->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->total->lowest->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->buy->total->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->buy->average->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->buy->highest->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->buy->lowest->toString()->toBe('5749.9950000000')
        ->net->forQuoteCurrency('USD')->sell->total->toString()->toBe('0.0000000000')
        ->net->forQuoteCurrency('USD')->sell->average->toString()->toBe('0.0000000000')
        ->net->forQuoteCurrency('USD')->sell->highest->toString()->toBe('0.0000000000')
        ->net->forQuoteCurrency('USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->net->forQuoteCurrency('EUR')->total->total->toString()->toBe('7000.9970000000')
        ->net->forQuoteCurrency('EUR')->total->average->toString()->toBe('3500.4985000000')
        ->net->forQuoteCurrency('EUR')->total->highest->toString()->toBe('4185.9990000000')
        ->net->forQuoteCurrency('EUR')->total->lowest->toString()->toBe('2814.9980000000')
        ->net->forQuoteCurrency('EUR')->buy->total->toString()->toBe('2814.9980000000')
        ->net->forQuoteCurrency('EUR')->buy->average->toString()->toBe('2814.9980000000')
        ->net->forQuoteCurrency('EUR')->buy->highest->toString()->toBe('2814.9980000000')
        ->net->forQuoteCurrency('EUR')->buy->lowest->toString()->toBe('2814.9980000000')
        ->net->forQuoteCurrency('EUR')->sell->total->toString()->toBe('4185.9990000000')
        ->net->forQuoteCurrency('EUR')->sell->average->toString()->toBe('4185.9990000000')
        ->net->forQuoteCurrency('EUR')->sell->highest->toString()->toBe('4185.9990000000')
        ->net->forQuoteCurrency('EUR')->sell->lowest->toString()->toBe('4185.9990000000')
        ->netProfits->total->toString()->toBe('12750.9920000000')
        ->netProfits->forPair('SHIB/USD')->toString()->toBe('5749.9950000000')
        ->netProfits->forPair('SHIB/EUR')->toString()->toBe('7000.9970000000')
        ->netProfits->forBaseCurrency('SHIB')->toString()->toBe('12750.9920000000')
        ->netProfits->forQuoteCurrency('USD')->toString()->toBe('5749.9950000000')
        ->netProfits->forQuoteCurrency('EUR')->toString()->toBe('7000.9970000000')
        ->netLosses->total->isZero()->toBeTrue()
        ->netLosses->forPair('SHIB/USD')->isZero()->toBeTrue()
        ->netLosses->forPair('SHIB/EUR')->isZero()->toBeTrue()
        ->netLosses->forBaseCurrency('SHIB')->isZero()->toBeTrue()
        ->netLosses->forQuoteCurrency('USD')->isZero()->toBeTrue()
        ->netLosses->forQuoteCurrency('EUR')->isZero()->toBeTrue()
        ->toArray()->toBe([
            'gross' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '13500.0000000000',
                            'average' => '4500.0000000000',
                            'highest' => [
                                'value' => '6000.0000000000',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '3000.0000000000',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'buy' => [
                            'total' => '9000.0000000000',
                            'average' => '4500.0000000000',
                            'highest' => [
                                'value' => '6000.0000000000',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '3000.0000000000',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'sell' => [
                            'total' => '4500.0000000000',
                            'average' => '4500.0000000000',
                            'highest' => [
                                'value' => '4500.0000000000',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '4500.0000000000',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                    'per_pair' => [
                        'SHIB/USD' => [
                            'total' => [
                                'total' => '6000.0000000000',
                                'average' => '6000.0000000000',
                                'highest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '6000.0000000000',
                                'average' => '6000.0000000000',
                                'highest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '6000.0000000000',
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
                                'total' => '7500.0000000000',
                                'average' => '3750.0000000000',
                                'highest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '3000.0000000000',
                                'average' => '3000.0000000000',
                                'highest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'sell' => [
                                'total' => '4500.0000000000',
                                'average' => '4500.0000000000',
                                'highest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                        ],
                    ],
                    'per_base_currency' => [
                        'SHIB' => [
                            'total' => [
                                'total' => '13500.0000000000',
                                'average' => '4500.0000000000',
                                'highest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '9000.0000000000',
                                'average' => '4500.0000000000',
                                'highest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'sell' => [
                                'total' => '4500.0000000000',
                                'average' => '4500.0000000000',
                                'highest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                        ],
                    ],
                    'per_quote_currency' => [
                        'USD' => [
                            'total' => [
                                'total' => '6000.0000000000',
                                'average' => '6000.0000000000',
                                'highest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '6000.0000000000',
                                'average' => '6000.0000000000',
                                'highest' => [
                                    'value' => '6000.0000000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '6000.0000000000',
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
                                'total' => '7500.0000000000',
                                'average' => '3750.0000000000',
                                'highest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '3000.0000000000',
                                'average' => '3000.0000000000',
                                'highest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '3000.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'sell' => [
                                'total' => '4500.0000000000',
                                'average' => '4500.0000000000',
                                'highest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '4500.0000000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                        ],
                    ],
                ],
                'profits' => [
                    'total' => '13500.0000000000',
                    'per_pair' => [
                        'SHIB/USD' => '6000.0000000000',
                        'SHIB/EUR' => '7500.0000000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '13500.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '6000.0000000000',
                        'EUR' => '7500.0000000000',
                    ],
                ],
                'losses' => [
                    'total' => '0.0000000000',
                    'per_pair' => [
                        'SHIB/USD' => '0.0000000000',
                        'SHIB/EUR' => '0.0000000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '0.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '0.0000000000',
                        'EUR' => '0.0000000000',
                    ],
                ],
            ],
            'net' => [
                'pnl' => [
                    'global' => [
                        'total' => [
                            'total' => '12750.9920000000',
                            'average' => '4250.3306666666',
                            'highest' => [
                                'value' => '5749.9950000000',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '2814.9980000000',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'buy' => [
                            'total' => '8564.9930000000',
                            'average' => '4282.4965000000',
                            'highest' => [
                                'value' => '5749.9950000000',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '2814.9980000000',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'sell' => [
                            'total' => '4185.9990000000',
                            'average' => '4185.9990000000',
                            'highest' => [
                                'value' => '4185.9990000000',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '4185.9990000000',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                    'per_pair' => [
                        'SHIB/USD' => [
                            'total' => [
                                'total' => '5749.9950000000',
                                'average' => '5749.9950000000',
                                'highest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '5749.9950000000',
                                'average' => '5749.9950000000',
                                'highest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '5749.9950000000',
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
                                'total' => '7000.9970000000',
                                'average' => '3500.4985000000',
                                'highest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '2814.9980000000',
                                'average' => '2814.9980000000',
                                'highest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'sell' => [
                                'total' => '4185.9990000000',
                                'average' => '4185.9990000000',
                                'highest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                        ],
                    ],
                    'per_base_currency' => [
                        'SHIB' => [
                            'total' => [
                                'total' => '12750.9920000000',
                                'average' => '4250.3306666666',
                                'highest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '8564.9930000000',
                                'average' => '4282.4965000000',
                                'highest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'sell' => [
                                'total' => '4185.9990000000',
                                'average' => '4185.9990000000',
                                'highest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                        ],
                    ],
                    'per_quote_currency' => [
                        'USD' => [
                            'total' => [
                                'total' => '5749.9950000000',
                                'average' => '5749.9950000000',
                                'highest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '5749.9950000000',
                                'average' => '5749.9950000000',
                                'highest' => [
                                    'value' => '5749.9950000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '5749.9950000000',
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
                                'total' => '7000.9970000000',
                                'average' => '3500.4985000000',
                                'highest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '2814.9980000000',
                                'average' => '2814.9980000000',
                                'highest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '2814.9980000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'sell' => [
                                'total' => '4185.9990000000',
                                'average' => '4185.9990000000',
                                'highest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '4185.9990000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                        ],
                    ],
                ],
                'profits' => [
                    'total' => '12750.9920000000',
                    'per_pair' => [
                        'SHIB/USD' => '5749.9950000000',
                        'SHIB/EUR' => '7000.9970000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '12750.9920000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '5749.9950000000',
                        'EUR' => '7000.9970000000',
                    ],
                ],
                'losses' => [
                    'total' => '0.0000000000',
                    'per_pair' => [
                        'SHIB/USD' => '0.0000000000',
                        'SHIB/EUR' => '0.0000000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '0.0000000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '0.0000000000',
                        'EUR' => '0.0000000000',
                    ],
                ],
            ],
        ]);
})->with('closed-with-returns-40-20-15');

it('correctly returns profits and losses', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->realizedProfitAndLoss)
        ->toBeInstanceOf(ProfitAndLoss::class)
        ->toArray()->toBe([
            'gross' => [
                'pnl' => [
                    'global' => [
                        'total' => [
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
                        ],
                        'buy' => [
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
                        'SHIB/EUR' => [
                            'total' => [
                                'total' => '0.0030000000',
                                'average' => '0.0015000000',
                                'highest' => [
                                    'value' => '0.0230000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-0.0200000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '0.0030000000',
                                'average' => '0.0015000000',
                                'highest' => [
                                    'value' => '0.0230000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-0.0200000000',
                                    'pair' => 'SHIB/EUR',
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
                            ],
                            'buy' => [
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
                        'EUR' => [
                            'total' => [
                                'total' => '0.0030000000',
                                'average' => '0.0015000000',
                                'highest' => [
                                    'value' => '0.0230000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-0.0200000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '0.0030000000',
                                'average' => '0.0015000000',
                                'highest' => [
                                    'value' => '0.0230000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-0.0200000000',
                                    'pair' => 'SHIB/EUR',
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
                    'total' => '0.1880000000',
                    'per_pair' => [
                        'SHIB/USD' => '0.1650000000',
                        'SHIB/EUR' => '0.0230000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '0.1880000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '0.1650000000',
                        'EUR' => '0.0230000000',
                    ],
                ],
                'losses' => [
                    'total' => '-0.0200000000',
                    'per_pair' => [
                        'SHIB/EUR' => '-0.0200000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '-0.0200000000',
                    ],
                    'per_quote_currency' => [
                        'EUR' => '-0.0200000000',
                    ],
                ],
            ],
            'net' => [
                'pnl' => [
                    'global' => [
                        'total' => [
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
                        ],
                        'buy' => [
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
                                'total' => '-14.8350000000',
                                'average' => '-14.8350000000',
                                'highest' => [
                                    'value' => '-14.8350000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '-14.8350000000',
                                    'pair' => 'SHIB/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '-14.8350000000',
                                'average' => '-14.8350000000',
                                'highest' => [
                                    'value' => '-14.8350000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '-14.8350000000',
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
                                'total' => '-16.4970000000',
                                'average' => '-8.2485000000',
                                'highest' => [
                                    'value' => '-7.9770000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-8.5200000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '-16.4970000000',
                                'average' => '-8.2485000000',
                                'highest' => [
                                    'value' => '-7.9770000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-8.5200000000',
                                    'pair' => 'SHIB/EUR',
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
                            ],
                            'buy' => [
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
                                'total' => '-14.8350000000',
                                'average' => '-14.8350000000',
                                'highest' => [
                                    'value' => '-14.8350000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '-14.8350000000',
                                    'pair' => 'SHIB/USD',
                                ],
                            ],
                            'buy' => [
                                'total' => '-14.8350000000',
                                'average' => '-14.8350000000',
                                'highest' => [
                                    'value' => '-14.8350000000',
                                    'pair' => 'SHIB/USD',
                                ],
                                'lowest' => [
                                    'value' => '-14.8350000000',
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
                                'total' => '-16.4970000000',
                                'average' => '-8.2485000000',
                                'highest' => [
                                    'value' => '-7.9770000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-8.5200000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                            ],
                            'buy' => [
                                'total' => '-16.4970000000',
                                'average' => '-8.2485000000',
                                'highest' => [
                                    'value' => '-7.9770000000',
                                    'pair' => 'SHIB/EUR',
                                ],
                                'lowest' => [
                                    'value' => '-8.5200000000',
                                    'pair' => 'SHIB/EUR',
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
                    'total' => '0.0000000000',
                    'per_pair' => [],
                    'per_base_currency' => [],
                    'per_quote_currency' => [],
                ],
                'losses' => [
                    'total' => '-31.3320000000',
                    'per_pair' => [
                        'SHIB/USD' => '-14.8350000000',
                        'SHIB/EUR' => '-16.4970000000',
                    ],
                    'per_base_currency' => [
                        'SHIB' => '-31.3320000000',
                    ],
                    'per_quote_currency' => [
                        'USD' => '-14.8350000000',
                        'EUR' => '-16.4970000000',
                    ],
                ],
            ],
        ]);
})->with('different-highest-and-lowest');
