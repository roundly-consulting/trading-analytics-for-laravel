<?php

declare(strict_types=1);

use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics;
use RoundlyConsulting\TradingAnalytics\Analytics\Counts;
use RoundlyConsulting\TradingAnalytics\Analytics\Streaks;
use RoundlyConsulting\TradingAnalytics\Analytics\Wins;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidEngineException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnknownCalculatorException;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsManager;

// No toReachEveryAction(): trading-analytics has no src/Actions — it is a stateless
// calculation engine (the skill's exemption). No toBeFakeable() either: nothing to fake,
// the engine has no side effects (the README says so).
it('documents its root', function (): void {
    expect(TradingAnalytics::class)->toDocumentItsRoot();
});

/**
 * @return list<array<string, string>>
 */
function tradeRows(): array
{
    return [
        [
            'base_currency' => 'BTC', 'quote_currency' => 'USD', 'open_price' => '100', 'close_price' => '110',
            'size' => '1', 'direction' => 'buy', 'open_time' => '2024-01-01 10:00:00', 'close_time' => '2024-01-01 11:00:00',
        ],
        [
            'base_currency' => 'BTC', 'quote_currency' => 'USD', 'open_price' => '100', 'close_price' => '90',
            'size' => '1', 'direction' => 'buy', 'open_time' => '2024-01-02 10:00:00', 'close_time' => '2024-01-02 11:00:00',
        ],
    ];
}

it('builds the engine from rows, trades or a mix', function (): void {
    $rows = tradeRows();
    $mixed = [Trade::fromArray($rows[0]), $rows[1]];

    $fromRows = TradingAnalytics::for($rows)->calculate();
    $fromMixed = TradingAnalytics::for($mixed)->calculate();
    $fromCollection = TradingAnalytics::for(Trade::collect($rows))->calculate();

    expect($fromRows)->toBeInstanceOf(Analytics::class)
        ->and((string) $fromRows->counts?->global->total)->toBe('2')
        ->and($fromMixed->toArray())->toEqual($fromRows->toArray())
        ->and($fromCollection->toArray())->toEqual($fromRows->toArray());
});

it('calculates in one call', function (): void {
    $analytics = TradingAnalytics::calculate(tradeRows());

    expect($analytics->hasBeenCalculated())->toBeTrue()
        ->and((string) $analytics->wins?->global->total)->toBe('1')
        ->and($analytics->streaks)->not->toBeNull();
});

it('calculates only the requested metrics and their dependencies', function (): void {
    $analytics = TradingAnalytics::calculate(tradeRows(), only: [Wins::class]);

    expect($analytics->wins)->not->toBeNull()
        ->and($analytics->counts)->not->toBeNull()
        ->and($analytics->streaks)->toBeNull();
});

it('refuses an unknown calculator in calculate()', function (): void {
    TradingAnalytics::calculate(tradeRows(), only: [stdClass::class]);
})->throws(UnknownCalculatorException::class);

it('maps rows to trades lazily', function (): void {
    $trades = TradingAnalytics::trades(tradeRows());

    expect($trades)->toBeInstanceOf(LazyCollection::class)
        ->and($trades->all())->each->toBeInstanceOf(Trade::class)
        ->and($trades->first()?->pair())->toBe('BTC/USD');
});

it('reports an invalid row only when the trades are iterated', function (): void {
    $trades = TradingAnalytics::trades([['base_currency' => 'BTC']]);

    expect(fn () => $trades->all())->toThrow(InvalidTradeException::class);
});

it('lists the metrics the engine runs', function (): void {
    expect(TradingAnalytics::metrics())->toContain(Counts::class, Streaks::class)
        ->and(TradingAnalytics::metrics())->toBe(Analytics::make(LazyCollection::empty())->metrics());
});

it('builds the base engine by default', function (): void {
    expect(TradingAnalytics::engine())->toBe(Analytics::class);
});

it('refuses an engine class that is not analytics', function (): void {
    TradingAnalytics::using(stdClass::class);
})->throws(InvalidEngineException::class, 'The class [stdClass] is not an analytics engine');

it('serves the same API from the injected manager', function (): void {
    $manager = app(TradingAnalyticsManager::class);

    expect($manager->calculate(tradeRows())->toArray())->toEqual(TradingAnalytics::calculate(tradeRows())->toArray())
        ->and($manager->metrics())->toBe(TradingAnalytics::metrics());
});

it('serves the same result through the engine without the manager', function (): void {
    expect(Analytics::for(Trade::collect(tradeRows()))->calculate()->toArray())
        ->toEqual(TradingAnalytics::calculate(tradeRows())->toArray());
});
