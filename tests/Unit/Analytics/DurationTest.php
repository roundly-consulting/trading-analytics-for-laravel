<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradesDuration;

it('correctly returns durations of trades', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->duration)
        ->toBeInstanceOf(TradesDuration::class)
        ->global->total->total->toString()->toBe('3000.00')
        ->global->total->average->toString()->toBe('428.57')
        ->global->total->highest->toString()->toBe('1800.00')
        ->global->total->highestPair->toBe('ETH/USD')
        ->global->total->lowest->toString()->toBe('300.00')
        ->global->total->lowestPair->toBe('BTC/USD')
        ->global->buy->total->toString()->toBe('2700.00')
        ->global->buy->average->toString()->toBe('450.00')
        ->global->buy->highest->toString()->toBe('1800.00')
        ->global->buy->highestPair->toBe('ETH/USD')
        ->global->buy->lowest->toString()->toBe('300.00')
        ->global->buy->lowestPair->toBe('BTC/USD')
        ->global->sell->total->toString()->toBe('300.00')
        ->global->sell->average->toString()->toBe('300.00')
        ->global->sell->highest->toString()->toBe('300.00')
        ->global->sell->highestPair->toBe('ETH/USD')
        ->global->sell->lowest->toString()->toBe('300.00')
        ->global->sell->lowestPair->toBe('ETH/USD')
        ->forPair('BTC/USD')->total->total->toString()->toBe('900.00')
        ->forPair('BTC/USD')->total->average->toString()->toBe('225.00')
        ->forPair('BTC/USD')->total->highest->toString()->toBe('600.00')
        ->forPair('BTC/USD')->total->highestPair->toBe('BTC/USD')
        ->forPair('BTC/USD')->total->lowest->toString()->toBe('300.00')
        ->forPair('BTC/USD')->total->lowestPair->toBe('BTC/USD')
        ->forPair('BTC/USD')->buy->total->toString()->toBe('900.00')
        ->forPair('BTC/USD')->buy->average->toString()->toBe('225.00')
        ->forPair('BTC/USD')->buy->highest->toString()->toBe('600.00')
        ->forPair('BTC/USD')->buy->highestPair->toBe('BTC/USD')
        ->forPair('BTC/USD')->buy->lowest->toString()->toBe('300.00')
        ->forPair('BTC/USD')->buy->lowestPair->toBe('BTC/USD')
        ->forPair('BTC/USD')->sell->total->toString()->toBe('0.00')
        ->forPair('BTC/USD')->sell->average->toString()->toBe('0.00')
        ->forPair('BTC/USD')->sell->highest->toString()->toBe('0.00')
        ->forPair('BTC/USD')->sell->highestPair->toBe('')
        ->forPair('BTC/USD')->sell->lowest->toString()->toBe('0.00')
        ->forPair('BTC/USD')->sell->lowestPair->toBe('')
        ->forPair('ETH/USD')->total->total->toString()->toBe('2100.00')
        ->forPair('ETH/USD')->total->average->toString()->toBe('700.00')
        ->forPair('ETH/USD')->total->highest->toString()->toBe('1800.00')
        ->forPair('ETH/USD')->total->highestPair->toBe('ETH/USD')
        ->forPair('ETH/USD')->total->lowest->toString()->toBe('300.00')
        ->forPair('ETH/USD')->total->lowestPair->toBe('ETH/USD')
        ->forPair('ETH/USD')->buy->total->toString()->toBe('1800.00')
        ->forPair('ETH/USD')->buy->average->toString()->toBe('900.00')
        ->forPair('ETH/USD')->buy->highest->toString()->toBe('1800.00')
        ->forPair('ETH/USD')->buy->highestPair->toBe('ETH/USD')
        ->forPair('ETH/USD')->buy->lowest->toString()->toBe('1800.00')
        ->forPair('ETH/USD')->buy->lowestPair->toBe('ETH/USD')
        ->forPair('ETH/USD')->sell->total->toString()->toBe('300.00')
        ->forPair('ETH/USD')->sell->average->toString()->toBe('300.00')
        ->forPair('ETH/USD')->sell->highest->toString()->toBe('300.00')
        ->forPair('ETH/USD')->sell->highestPair->toBe('ETH/USD')
        ->forPair('ETH/USD')->sell->lowest->toString()->toBe('300.00')
        ->forPair('ETH/USD')->sell->lowestPair->toBe('ETH/USD')
        ->forBaseCurrency('BTC')->total->total->toString()->toBe('900.00')
        ->forBaseCurrency('BTC')->total->average->toString()->toBe('225.00')
        ->forBaseCurrency('BTC')->total->highest->toString()->toBe('600.00')
        ->forBaseCurrency('BTC')->total->highestPair->toBe('BTC/USD')
        ->forBaseCurrency('BTC')->total->lowest->toString()->toBe('300.00')
        ->forBaseCurrency('BTC')->total->lowestPair->toBe('BTC/USD')
        ->forBaseCurrency('BTC')->buy->total->toString()->toBe('900.00')
        ->forBaseCurrency('BTC')->buy->average->toString()->toBe('225.00')
        ->forBaseCurrency('BTC')->buy->highest->toString()->toBe('600.00')
        ->forBaseCurrency('BTC')->buy->highestPair->toBe('BTC/USD')
        ->forBaseCurrency('BTC')->buy->lowest->toString()->toBe('300.00')
        ->forBaseCurrency('BTC')->buy->lowestPair->toBe('BTC/USD')
        ->forBaseCurrency('BTC')->sell->total->toString()->toBe('0.00')
        ->forBaseCurrency('BTC')->sell->average->toString()->toBe('0.00')
        ->forBaseCurrency('BTC')->sell->highest->toString()->toBe('0.00')
        ->forBaseCurrency('BTC')->sell->highestPair->toBe('')
        ->forBaseCurrency('BTC')->sell->lowest->toString()->toBe('0.00')
        ->forBaseCurrency('BTC')->sell->lowestPair->toBe('')
        ->forBaseCurrency('ETH')->total->total->toString()->toBe('2100.00')
        ->forBaseCurrency('ETH')->total->average->toString()->toBe('700.00')
        ->forBaseCurrency('ETH')->total->highest->toString()->toBe('1800.00')
        ->forBaseCurrency('ETH')->total->highestPair->toBe('ETH/USD')
        ->forBaseCurrency('ETH')->total->lowest->toString()->toBe('300.00')
        ->forBaseCurrency('ETH')->total->lowestPair->toBe('ETH/USD')
        ->forBaseCurrency('ETH')->buy->total->toString()->toBe('1800.00')
        ->forBaseCurrency('ETH')->buy->average->toString()->toBe('900.00')
        ->forBaseCurrency('ETH')->buy->highest->toString()->toBe('1800.00')
        ->forBaseCurrency('ETH')->buy->highestPair->toBe('ETH/USD')
        ->forBaseCurrency('ETH')->buy->lowest->toString()->toBe('1800.00')
        ->forBaseCurrency('ETH')->buy->lowestPair->toBe('ETH/USD')
        ->forBaseCurrency('ETH')->sell->total->toString()->toBe('300.00')
        ->forBaseCurrency('ETH')->sell->average->toString()->toBe('300.00')
        ->forBaseCurrency('ETH')->sell->highest->toString()->toBe('300.00')
        ->forBaseCurrency('ETH')->sell->highestPair->toBe('ETH/USD')
        ->forBaseCurrency('ETH')->sell->lowest->toString()->toBe('300.00')
        ->forBaseCurrency('ETH')->sell->lowestPair->toBe('ETH/USD')
        ->forQuoteCurrency('USD')->total->total->toString()->toBe('3000.00')
        ->forQuoteCurrency('USD')->total->average->toString()->toBe('428.57')
        ->forQuoteCurrency('USD')->total->highest->toString()->toBe('1800.00')
        ->forQuoteCurrency('USD')->total->highestPair->toBe('ETH/USD')
        ->forQuoteCurrency('USD')->total->lowest->toString()->toBe('300.00')
        ->forQuoteCurrency('USD')->total->lowestPair->toBe('BTC/USD')
        ->forQuoteCurrency('USD')->buy->total->toString()->toBe('2700.00')
        ->forQuoteCurrency('USD')->buy->average->toString()->toBe('450.00')
        ->forQuoteCurrency('USD')->buy->highest->toString()->toBe('1800.00')
        ->forQuoteCurrency('USD')->buy->highestPair->toBe('ETH/USD')
        ->forQuoteCurrency('USD')->buy->lowest->toString()->toBe('300.00')
        ->forQuoteCurrency('USD')->buy->lowestPair->toBe('BTC/USD')
        ->forQuoteCurrency('USD')->sell->total->toString()->toBe('300.00')
        ->forQuoteCurrency('USD')->sell->average->toString()->toBe('300.00')
        ->forQuoteCurrency('USD')->sell->highest->toString()->toBe('300.00')
        ->forQuoteCurrency('USD')->sell->highestPair->toBe('ETH/USD')
        ->forQuoteCurrency('USD')->sell->lowest->toString()->toBe('300.00')
        ->forQuoteCurrency('USD')->sell->lowestPair->toBe('ETH/USD')
        ->toArray()->toBe([
            'global' => [
                'total' => [
                    'total' => '3000.00',
                    'average' => '428.57',
                    'highest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '300.00',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'buy' => [
                    'total' => '2700.00',
                    'average' => '450.00',
                    'highest' => [
                        'value' => '1800.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '300.00',
                        'pair' => 'BTC/USD',
                    ],
                ],
                'sell' => [
                    'total' => '300.00',
                    'average' => '300.00',
                    'highest' => [
                        'value' => '300.00',
                        'pair' => 'ETH/USD',
                    ],
                    'lowest' => [
                        'value' => '300.00',
                        'pair' => 'ETH/USD',
                    ],
                ],
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'total' => [
                        'total' => '900.00',
                        'average' => '225.00',
                        'highest' => [
                            'value' => '600.00',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '900.00',
                        'average' => '225.00',
                        'highest' => [
                            'value' => '600.00',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.00',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.00',
                            'pair' => '',
                        ],
                    ],
                ],
                'ETH/USD' => [
                    'total' => [
                        'total' => '2100.00',
                        'average' => '700.00',
                        'highest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1800.00',
                        'average' => '900.00',
                        'highest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '300.00',
                        'average' => '300.00',
                        'highest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'total' => [
                        'total' => '900.00',
                        'average' => '225.00',
                        'highest' => [
                            'value' => '600.00',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '900.00',
                        'average' => '225.00',
                        'highest' => [
                            'value' => '600.00',
                            'pair' => 'BTC/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '0.00',
                        'average' => '0.00',
                        'highest' => [
                            'value' => '0.00',
                            'pair' => '',
                        ],
                        'lowest' => [
                            'value' => '0.00',
                            'pair' => '',
                        ],
                    ],
                ],
                'ETH' => [
                    'total' => [
                        'total' => '2100.00',
                        'average' => '700.00',
                        'highest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '1800.00',
                        'average' => '900.00',
                        'highest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '300.00',
                        'average' => '300.00',
                        'highest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'total' => [
                        'total' => '3000.00',
                        'average' => '428.57',
                        'highest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'buy' => [
                        'total' => '2700.00',
                        'average' => '450.00',
                        'highest' => [
                            'value' => '1800.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'BTC/USD',
                        ],
                    ],
                    'sell' => [
                        'total' => '300.00',
                        'average' => '300.00',
                        'highest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                        'lowest' => [
                            'value' => '300.00',
                            'pair' => 'ETH/USD',
                        ],
                    ],
                ],
            ],
        ]);
})->with('frequency-test-trades');
