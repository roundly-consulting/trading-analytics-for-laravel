<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results\TradingFrequency;

it('correctly returns frequency of trades', function (LazyCollection $trades) {
    $analytics = new Analytics($trades);
    $analytics->calculate();

    expect($analytics->frequency)
        ->toBeInstanceOf(TradingFrequency::class)
        ->total->toString()->toBe('2.1 per week')
        ->total->toRawString()->toBe('2.1')
        ->total->suffix->toBe('per week')
        ->forPair('BTC/USD')->toString()->toBe('1.1 per week')
        ->forPair('BTC/USD')->toRawString()->toBe('1.1')
        ->forPair('BTC/USD')->suffix->toBe('per week')
        ->forPair('ETH/USD')->toString()->toBe('3.1 per month')
        ->forPair('ETH/USD')->toRawString()->toBe('3.1')
        ->forPair('ETH/USD')->suffix->toBe('per month')
        ->forBaseCurrency('BTC')->toString()->toBe('1.1 per week')
        ->forBaseCurrency('BTC')->toRawString()->toBe('1.1')
        ->forBaseCurrency('BTC')->suffix->toBe('per week')
        ->forBaseCurrency('ETH')->toString()->toBe('3.1 per month')
        ->forBaseCurrency('ETH')->toRawString()->toBe('3.1')
        ->forBaseCurrency('ETH')->suffix->toBe('per month')
        ->forQuoteCurrency('USD')->toString()->toBe('2.1 per week')
        ->forQuoteCurrency('USD')->toRawString()->toBe('2.1')
        ->forQuoteCurrency('USD')->suffix->toBe('per week')
        ->toArray()->toBe([
            'total' => [
                'value' => '2.1',
                'scale' => 1,
                'prefix' => '',
                'suffix' => 'per week',
                'formatted' => '2.1 per week',
            ],
            'per_pair' => [
                'BTC/USD' => [
                    'value' => '1.1',
                    'scale' => 1,
                    'prefix' => '',
                    'suffix' => 'per week',
                    'formatted' => '1.1 per week',
                ],
                'ETH/USD' => [
                    'value' => '3.1',
                    'scale' => 1,
                    'prefix' => '',
                    'suffix' => 'per month',
                    'formatted' => '3.1 per month',
                ],
            ],
            'per_base_currency' => [
                'BTC' => [
                    'value' => '1.1',
                    'scale' => 1,
                    'prefix' => '',
                    'suffix' => 'per week',
                    'formatted' => '1.1 per week',
                ],
                'ETH' => [
                    'value' => '3.1',
                    'scale' => 1,
                    'prefix' => '',
                    'suffix' => 'per month',
                    'formatted' => '3.1 per month',
                ],
            ],
            'per_quote_currency' => [
                'USD' => [
                    'value' => '2.1',
                    'scale' => 1,
                    'prefix' => '',
                    'suffix' => 'per week',
                    'formatted' => '2.1 per week',
                ],
            ],
        ]);
})->with('frequency-test-trades');
