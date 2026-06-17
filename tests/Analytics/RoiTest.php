<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\CumulativeReturn;

it('correctly returns rois', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->cumulativeReturn)
        ->toBeInstanceOf(CumulativeReturn::class)
        ->gross->toBeInstanceOf(NumericDirectionalAggregatesByCurrency::class)
        ->net->toBeInstanceOf(NumericDirectionalAggregatesByCurrency::class)
        ->gross->global->total->total->toString()->toBe('93.20')
        ->gross->global->total->average->toString()->toBe('24.54')
        ->gross->global->total->highest->toString()->toBe('93.20')
        ->gross->global->total->lowest->toString()->toBe('40.00')
        ->gross->global->buy->total->toString()->toBe('68.00')
        ->gross->global->buy->average->toString()->toBe('29.61')
        ->gross->global->buy->highest->toString()->toBe('68.00')
        ->gross->global->buy->lowest->toString()->toBe('40.00')
        ->gross->global->sell->total->toString()->toBe('15.00')
        ->gross->global->sell->average->toString()->toBe('15.00')
        ->gross->global->sell->highest->toString()->toBe('15.00')
        ->gross->global->sell->lowest->toString()->toBe('15.00')
        ->gross->forPair('SHIB/USD')->total->total->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->total->average->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->total->highest->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->total->lowest->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->buy->total->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->buy->average->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->buy->highest->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->buy->lowest->toString()->toBe('40.00')
        ->gross->forPair('SHIB/USD')->sell->total->toString()->toBe('0.00')
        ->gross->forPair('SHIB/USD')->sell->average->toString()->toBe('0.00')
        ->gross->forPair('SHIB/USD')->sell->highest->toString()->toBe('0.0000000000')
        ->gross->forPair('SHIB/USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->gross->forPair('SHIB/EUR')->total->total->toString()->toBe('38.00')
        ->gross->forPair('SHIB/EUR')->total->average->toString()->toBe('17.47')
        ->gross->forPair('SHIB/EUR')->total->highest->toString()->toBe('38.00')
        ->gross->forPair('SHIB/EUR')->total->lowest->toString()->toBe('20.00')
        ->gross->forPair('SHIB/EUR')->buy->total->toString()->toBe('20.00')
        ->gross->forPair('SHIB/EUR')->buy->average->toString()->toBe('20.00')
        ->gross->forPair('SHIB/EUR')->buy->highest->toString()->toBe('20.00')
        ->gross->forPair('SHIB/EUR')->buy->lowest->toString()->toBe('20.00')
        ->gross->forPair('SHIB/EUR')->sell->total->toString()->toBe('15.00')
        ->gross->forPair('SHIB/EUR')->sell->average->toString()->toBe('15.00')
        ->gross->forPair('SHIB/EUR')->sell->highest->toString()->toBe('15.00')
        ->gross->forPair('SHIB/EUR')->sell->lowest->toString()->toBe('15.00')
        ->gross->forBaseCurrency('SHIB')->total->total->toString()->toBe('93.20')
        ->gross->forBaseCurrency('SHIB')->total->average->toString()->toBe('24.54')
        ->gross->forBaseCurrency('SHIB')->total->highest->toString()->toBe('93.20')
        ->gross->forBaseCurrency('SHIB')->total->lowest->toString()->toBe('40.00')
        ->gross->forBaseCurrency('SHIB')->buy->total->toString()->toBe('68.00')
        ->gross->forBaseCurrency('SHIB')->buy->average->toString()->toBe('29.61')
        ->gross->forBaseCurrency('SHIB')->buy->highest->toString()->toBe('68.00')
        ->gross->forBaseCurrency('SHIB')->buy->lowest->toString()->toBe('40.00')
        ->gross->forBaseCurrency('SHIB')->sell->total->toString()->toBe('15.00')
        ->gross->forBaseCurrency('SHIB')->sell->average->toString()->toBe('15.00')
        ->gross->forBaseCurrency('SHIB')->sell->highest->toString()->toBe('15.00')
        ->gross->forBaseCurrency('SHIB')->sell->lowest->toString()->toBe('15.00')
        ->gross->forQuoteCurrency('USD')->total->total->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->total->average->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->total->highest->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->total->lowest->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->buy->total->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->buy->average->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->buy->highest->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->buy->lowest->toString()->toBe('40.00')
        ->gross->forQuoteCurrency('USD')->sell->total->toString()->toBe('0.00')
        ->gross->forQuoteCurrency('USD')->sell->average->toString()->toBe('0.00')
        ->gross->forQuoteCurrency('USD')->sell->highest->toString()->toBe('0.0000000000')
        ->gross->forQuoteCurrency('USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->gross->forQuoteCurrency('EUR')->total->total->toString()->toBe('38.00')
        ->gross->forQuoteCurrency('EUR')->total->average->toString()->toBe('17.47')
        ->gross->forQuoteCurrency('EUR')->total->highest->toString()->toBe('38.00')
        ->gross->forQuoteCurrency('EUR')->total->lowest->toString()->toBe('20.00')
        ->gross->forQuoteCurrency('EUR')->buy->total->toString()->toBe('20.00')
        ->gross->forQuoteCurrency('EUR')->buy->average->toString()->toBe('20.00')
        ->gross->forQuoteCurrency('EUR')->buy->highest->toString()->toBe('20.00')
        ->gross->forQuoteCurrency('EUR')->buy->lowest->toString()->toBe('20.00')
        ->gross->forQuoteCurrency('EUR')->sell->total->toString()->toBe('15.00')
        ->gross->forQuoteCurrency('EUR')->sell->average->toString()->toBe('15.00')
        ->gross->forQuoteCurrency('EUR')->sell->highest->toString()->toBe('15.00')
        ->gross->forQuoteCurrency('EUR')->sell->lowest->toString()->toBe('15.00')
        ->net->global->total->total->toString()->toBe('87.21')
        ->net->global->total->average->toString()->toBe('23.24')
        ->net->global->total->highest->toString()->toBe('87.21')
        ->net->global->total->lowest->toString()->toBe('38.33')
        ->net->global->buy->total->toString()->toBe('64.29')
        ->net->global->buy->average->toString()->toBe('28.17')
        ->net->global->buy->highest->toString()->toBe('64.29')
        ->net->global->buy->lowest->toString()->toBe('38.33')
        ->net->global->sell->total->toString()->toBe('13.95')
        ->net->global->sell->average->toString()->toBe('13.95')
        ->net->global->sell->highest->toString()->toBe('13.95')
        ->net->global->sell->lowest->toString()->toBe('13.95')
        ->net->forPair('SHIB/USD')->total->total->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->total->average->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->total->highest->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->total->lowest->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->buy->total->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->buy->average->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->buy->highest->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->buy->lowest->toString()->toBe('38.33')
        ->net->forPair('SHIB/USD')->sell->total->toString()->toBe('0.00')
        ->net->forPair('SHIB/USD')->sell->average->toString()->toBe('0.00')
        ->net->forPair('SHIB/USD')->sell->highest->toString()->toBe('0.0000000000')
        ->net->forPair('SHIB/USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->net->forPair('SHIB/EUR')->total->total->toString()->toBe('35.33')
        ->net->forPair('SHIB/EUR')->total->average->toString()->toBe('16.33')
        ->net->forPair('SHIB/EUR')->total->highest->toString()->toBe('35.33')
        ->net->forPair('SHIB/EUR')->total->lowest->toString()->toBe('18.76')
        ->net->forPair('SHIB/EUR')->buy->total->toString()->toBe('18.76')
        ->net->forPair('SHIB/EUR')->buy->average->toString()->toBe('18.76')
        ->net->forPair('SHIB/EUR')->buy->highest->toString()->toBe('18.76')
        ->net->forPair('SHIB/EUR')->buy->lowest->toString()->toBe('18.76')
        ->net->forPair('SHIB/EUR')->sell->total->toString()->toBe('13.95')
        ->net->forPair('SHIB/EUR')->sell->average->toString()->toBe('13.95')
        ->net->forPair('SHIB/EUR')->sell->highest->toString()->toBe('13.95')
        ->net->forPair('SHIB/EUR')->sell->lowest->toString()->toBe('13.95')
        ->net->forBaseCurrency('SHIB')->total->total->toString()->toBe('87.21')
        ->net->forBaseCurrency('SHIB')->total->average->toString()->toBe('23.24')
        ->net->forBaseCurrency('SHIB')->total->highest->toString()->toBe('87.21')
        ->net->forBaseCurrency('SHIB')->total->lowest->toString()->toBe('38.33')
        ->net->forBaseCurrency('SHIB')->buy->total->toString()->toBe('64.29')
        ->net->forBaseCurrency('SHIB')->buy->average->toString()->toBe('28.17')
        ->net->forBaseCurrency('SHIB')->buy->highest->toString()->toBe('64.29')
        ->net->forBaseCurrency('SHIB')->buy->lowest->toString()->toBe('38.33')
        ->net->forBaseCurrency('SHIB')->sell->total->toString()->toBe('13.95')
        ->net->forBaseCurrency('SHIB')->sell->average->toString()->toBe('13.95')
        ->net->forBaseCurrency('SHIB')->sell->highest->toString()->toBe('13.95')
        ->net->forBaseCurrency('SHIB')->sell->lowest->toString()->toBe('13.95')
        ->net->forQuoteCurrency('USD')->total->total->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->total->average->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->total->highest->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->total->lowest->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->buy->total->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->buy->average->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->buy->highest->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->buy->lowest->toString()->toBe('38.33')
        ->net->forQuoteCurrency('USD')->sell->total->toString()->toBe('0.00')
        ->net->forQuoteCurrency('USD')->sell->average->toString()->toBe('0.00')
        ->net->forQuoteCurrency('USD')->sell->highest->toString()->toBe('0.0000000000')
        ->net->forQuoteCurrency('USD')->sell->lowest->toString()->toBe('0.0000000000')
        ->net->forQuoteCurrency('EUR')->total->total->toString()->toBe('35.33')
        ->net->forQuoteCurrency('EUR')->total->average->toString()->toBe('16.33')
        ->net->forQuoteCurrency('EUR')->total->highest->toString()->toBe('35.33')
        ->net->forQuoteCurrency('EUR')->total->lowest->toString()->toBe('18.76')
        ->net->forQuoteCurrency('EUR')->buy->total->toString()->toBe('18.76')
        ->net->forQuoteCurrency('EUR')->buy->average->toString()->toBe('18.76')
        ->net->forQuoteCurrency('EUR')->buy->highest->toString()->toBe('18.76')
        ->net->forQuoteCurrency('EUR')->buy->lowest->toString()->toBe('18.76')
        ->net->forQuoteCurrency('EUR')->sell->total->toString()->toBe('13.95')
        ->net->forQuoteCurrency('EUR')->sell->average->toString()->toBe('13.95')
        ->net->forQuoteCurrency('EUR')->sell->highest->toString()->toBe('13.95')
        ->net->forQuoteCurrency('EUR')->sell->lowest->toString()->toBe('13.95')
        ->toArray()->toBe([
            'gross' => [
                'global' => [
                    'total' => [
                        'total' => '93.20',
                        'average' => '24.54',
                        'highest' => [
                            'value' => '93.20',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '40.00',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '68.00',
                        'average' => '29.61',
                        'highest' => [
                            'value' => '68.00',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '40.00',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '15.00',
                        'average' => '15.00',
                        'highest' => [
                            'value' => '15.00',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '15.00',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                ],
                'per_pair' => [
                    'SHIB/USD' => [
                        'total' => [
                            'total' => '40.00',
                            'average' => '40.00',
                            'highest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '40.00',
                            'average' => '40.00',
                            'highest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '0.00',
                            'average' => '0.00',
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
                            'total' => '38.00',
                            'average' => '17.47',
                            'highest' => [
                                'value' => '38.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '20.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'buy' => [
                            'total' => '20.00',
                            'average' => '20.00',
                            'highest' => [
                                'value' => '20.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '20.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'sell' => [
                            'total' => '15.00',
                            'average' => '15.00',
                            'highest' => [
                                'value' => '15.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '15.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                ],
                'per_base_currency' => [
                    'SHIB' => [
                        'total' => [
                            'total' => '93.20',
                            'average' => '24.54',
                            'highest' => [
                                'value' => '93.20',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '68.00',
                            'average' => '29.61',
                            'highest' => [
                                'value' => '68.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '15.00',
                            'average' => '15.00',
                            'highest' => [
                                'value' => '15.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '15.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                ],
                'per_quote_currency' => [
                    'USD' => [
                        'total' => [
                            'total' => '40.00',
                            'average' => '40.00',
                            'highest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '40.00',
                            'average' => '40.00',
                            'highest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '40.00',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '0.00',
                            'average' => '0.00',
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
                            'total' => '38.00',
                            'average' => '17.47',
                            'highest' => [
                                'value' => '38.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '20.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'buy' => [
                            'total' => '20.00',
                            'average' => '20.00',
                            'highest' => [
                                'value' => '20.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '20.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'sell' => [
                            'total' => '15.00',
                            'average' => '15.00',
                            'highest' => [
                                'value' => '15.00',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '15.00',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                ],
            ],
            'net' => [
                'global' => [
                    'total' => [
                        'total' => '87.21',
                        'average' => '23.24',
                        'highest' => [
                            'value' => '87.21',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '38.33',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '64.29',
                        'average' => '28.17',
                        'highest' => [
                            'value' => '64.29',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '38.33',
                            'pair' => 'SHIB/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '13.95',
                        'average' => '13.95',
                        'highest' => [
                            'value' => '13.95',
                            'pair' => 'SHIB/EUR',
                        ],
                        'lowest' => [
                            'value' => '13.95',
                            'pair' => 'SHIB/EUR',
                        ],
                    ],
                ],
                'per_pair' => [
                    'SHIB/USD' => [
                        'total' => [
                            'total' => '38.33',
                            'average' => '38.33',
                            'highest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '38.33',
                            'average' => '38.33',
                            'highest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '0.00',
                            'average' => '0.00',
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
                            'total' => '35.33',
                            'average' => '16.33',
                            'highest' => [
                                'value' => '35.33',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '18.76',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'buy' => [
                            'total' => '18.76',
                            'average' => '18.76',
                            'highest' => [
                                'value' => '18.76',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '18.76',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'sell' => [
                            'total' => '13.95',
                            'average' => '13.95',
                            'highest' => [
                                'value' => '13.95',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '13.95',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                ],
                'per_base_currency' => [
                    'SHIB' => [
                        'total' => [
                            'total' => '87.21',
                            'average' => '23.24',
                            'highest' => [
                                'value' => '87.21',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '64.29',
                            'average' => '28.17',
                            'highest' => [
                                'value' => '64.29',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '13.95',
                            'average' => '13.95',
                            'highest' => [
                                'value' => '13.95',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '13.95',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                ],
                'per_quote_currency' => [
                    'USD' => [
                        'total' => [
                            'total' => '38.33',
                            'average' => '38.33',
                            'highest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'buy' => [
                            'total' => '38.33',
                            'average' => '38.33',
                            'highest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                            'lowest' => [
                                'value' => '38.33',
                                'pair' => 'SHIB/USD',
                            ],
                        ],
                        'sell' => [
                            'total' => '0.00',
                            'average' => '0.00',
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
                            'total' => '35.33',
                            'average' => '16.33',
                            'highest' => [
                                'value' => '35.33',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '18.76',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'buy' => [
                            'total' => '18.76',
                            'average' => '18.76',
                            'highest' => [
                                'value' => '18.76',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '18.76',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                        'sell' => [
                            'total' => '13.95',
                            'average' => '13.95',
                            'highest' => [
                                'value' => '13.95',
                                'pair' => 'SHIB/EUR',
                            ],
                            'lowest' => [
                                'value' => '13.95',
                                'pair' => 'SHIB/EUR',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
})->with('closed-with-returns-40-20-15');
