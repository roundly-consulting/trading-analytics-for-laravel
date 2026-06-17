<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;

it('returns correctly pair', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade->pair())->toBe('SHIB/USD');
})->with('closed-with-returns-40-20-15');

it('returns correctly whether trade is realized or open', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade)
        ->isOpen()->toBeTrue()
        ->isRealized()->toBeFalse();

    $trade = $trades->get(1);

    expect($trade)
        ->isOpen()->toBeFalse()
        ->isRealized()->toBeTrue();
})->with('open-and-closed-trades');

it('calculates correctly pnl', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade)
        ->profitAndLoss()->toString()->toBe('6000.0000000000')
        ->profitAndLoss(true)->toString()->toBe('5749.9950000000');

    $trade = $trades->get(1);

    expect($trade)
        ->profitAndLoss()->toString()->toBe('3000.0000000000')
        ->profitAndLoss(true)->toString()->toBe('2814.9980000000');

    $trade = $trades->get(2);

    expect($trade)
        ->profitAndLoss()->toString()->toBe('4500.0000000000')
        ->profitAndLoss(true)->toString()->toBe('4185.9990000000');
})->with('closed-with-returns-40-20-15');

it('calculates correctly roi', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade)
        ->roi(asPercentage: false)->toString()->toBe('0.4000000000')
        ->roi()->toString()->toBe('40.00')
        ->roi(true)->toString()->toBe('38.33')
        ->roi(subtractCommissions: true, asPercentage: false)->toString()->toBe('0.3833330000');

    $trade = $trades->get(1);

    expect($trade)
        ->roi(asPercentage: false)->toString()->toBe('0.2000000000')
        ->roi()->toString()->toBe('20.00')
        ->roi(true)->toString()->toBe('18.77')
        ->roi(subtractCommissions: true, asPercentage: false)->toString()->toBe('0.1876665333');

    $trade = $trades->get(2);

    expect($trade)
        ->roi(asPercentage: false)->toString()->toBe('0.1500000000')
        ->roi()->toString()->toBe('15.00')
        ->roi(true)->toString()->toBe('13.95')
        ->roi(subtractCommissions: true, asPercentage: false)->toString()->toBe('0.1395333000');
})->with('closed-with-returns-40-20-15');

it('returns trade as array', function (LazyCollection $trades) {
    /** @var Trade $trade */
    $trade = $trades->first();

    expect($trade->toArray())->toBe([
        'base_currency' => 'SHIB',
        'quote_currency' => 'USD',
        'open_price' => '0.0000100000',
        'close_price' => '0.0000140000',
        'size' => '1500000000.0000000000',
        'direction' => 'buy',
        'open_time' => '2023-01-15 12:30:00',
        'close_time' => '2023-01-15 14:30:00',
        'commission' => '250.0050000000',
        'is_realized' => true,
        'is_open' => false,
        'pnl' => [
            'gross' => '6000.0000000000',
            'net' => '5749.9950000000',
        ],
        'roi' => [
            'gross' => '40.00',
            'net' => '38.33',
        ],
    ]);

})->with('closed-with-returns-40-20-15');
