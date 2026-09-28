<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use RoundlyConsulting\TradingAnalytics\Analytics\MaxDrawdown;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade;
use RoundlyConsulting\TradingAnalytics\Enums\Direction;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidChunkSizeException;
use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidTradeException;
use RoundlyConsulting\TradingAnalytics\Exceptions\TradingAnalyticsException;
use RoundlyConsulting\TradingAnalytics\Exceptions\UnorderedTradeSourceException;
use RoundlyConsulting\TradingAnalytics\Facades\TradingAnalytics;
use RoundlyConsulting\TradingAnalytics\Tests\Support\Side;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradeRecord;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradesTable;
use RoundlyConsulting\TradingAnalytics\Tests\Support\TradingAccount;
use RoundlyConsulting\TradingAnalytics\TradingAnalyticsManager;

/**
 * Trade sources beyond arrays: database rows (`stdClass`), Eloquent models with casts,
 * `Arrayable`s and plain objects, and the query builders themselves — streamed with `lazy()`
 * in bounded pages, and refused when unordered because the metrics depend on trade order.
 */

/**
 * Five realized BUY trades, one per day, whose net P&L runs +10, -5, +100, -50, +20. In close
 * order the 50 drop comes off a peak of 105 (a 47.619% drawdown); read backwards it would come
 * off a peak of 20 (250%) — a sequence that never happened, which the engine refuses.
 *
 * @return list<array<string, string|null>>
 */
function orderSensitiveRows(): array
{
    $row = static fn (string $close, int $day): array => [
        'base_currency' => 'BTC',
        'quote_currency' => 'USD',
        'open_price' => '100',
        'close_price' => $close,
        'size' => '1',
        'direction' => 'buy',
        'open_time' => sprintf('2024-03-%02d 10:00:00', $day),
        'commission' => null,
        'close_time' => sprintf('2024-03-%02d 11:00:00', $day),
    ];

    return [$row('110', 1), $row('95', 2), $row('200', 3), $row('50', 4), $row('120', 5)];
}

/** @return array<string, string|null> */
function tradeRow(): array
{
    return orderSensitiveRows()[0];
}

beforeEach(function (): void {
    TradesTable::create();
});

// ---------------------------------------------------------------------------------------
// Rows that are not arrays
// ---------------------------------------------------------------------------------------

it('maps a stdClass database row to a trade', function (): void {
    $trade = Trade::fromRow((object) tradeRow());

    expect($trade->pair())->toBe('BTC/USD')
        ->and($trade->direction)->toBe(Direction::BUY)
        ->and($trade->closeTime?->toDateTimeString())->toBe('2024-03-01 11:00:00');
});

it('collects query-builder rows, as the README shows', function (): void {
    TradesTable::seed(orderSensitiveRows());

    $fromRows = TradingAnalytics::calculate(DB::table('trades')->orderBy('id')->lazy());

    expect($fromRows->toArray())->toBe(TradingAnalytics::calculate(orderSensitiveRows())->toArray())
        ->and(Trade::collect(DB::table('trades')->orderBy('id')->lazy())->count())->toBe(5);
});

it('reads an eloquent model through its casts', function (): void {
    $account = TradingAccount::query()->create(['name' => 'desk']);
    TradesTable::seed([['direction' => 'sell', 'commission' => '1.25'] + tradeRow()], $account->id);

    $record = TradeRecord::query()->firstOrFail();

    expect($record->getAttribute('direction'))->toBe(Side::Short)
        ->and($record->getAttribute('open_time'))->toBeInstanceOf(DateTimeImmutable::class);

    $trade = Trade::fromRow($record);

    expect($trade->direction)->toBe(Direction::SELL)
        ->and($trade->openTime)->toBeInstanceOf(Carbon::class)
        ->and($trade->openTime->toDateTimeString())->toBe('2024-03-01 10:00:00')
        ->and($trade->closeTime?->toDateTimeString())->toBe('2024-03-01 11:00:00')
        ->and($trade->openPrice->toRawString())->toBe('100.0000000000')
        ->and($trade->commission?->toRawString())->toBe('1.2500000000');
});

it('reads a model field through an accessor when the column is named differently', function (): void {
    $model = new class extends Model
    {
        protected $guarded = [];

        protected function openTime(): Attribute
        {
            return Attribute::get(fn (): mixed => $this->getAttributes()['opened_at'] ?? null);
        }
    };

    $attributes = tradeRow();
    $attributes['opened_at'] = $attributes['open_time'];
    unset($attributes['open_time']);

    $trade = Trade::fromRow($model->forceFill($attributes));

    expect($trade->openTime->toDateTimeString())->toBe('2024-03-01 10:00:00');
});

it('refuses a model missing a required field', function (): void {
    $attributes = tradeRow();
    unset($attributes['size']);

    expect(fn () => Trade::fromRow(new TradeRecord($attributes)))
        ->toThrow(InvalidTradeException::class, "missing the required 'size' field");
});

it('treats an unselected optional model column as absent', function (): void {
    TradesTable::seed([tradeRow()]);

    Model::preventAccessingMissingAttributes();

    try {
        $record = TradeRecord::query()
            ->select(['base_currency', 'quote_currency', 'open_price', 'close_price', 'size', 'direction', 'open_time'])
            ->firstOrFail();

        expect(Trade::fromRow($record)->isOpen())->toBeTrue();
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('maps an arrayable and a plain object', function (): void {
    $arrayable = new class(tradeRow()) implements Arrayable
    {
        /** @param array<string, mixed> $attributes */
        public function __construct(private readonly array $attributes) {}

        /** @return array<string, mixed> */
        public function toArray(): array
        {
            return $this->attributes;
        }
    };

    $object = new class
    {
        public string $base_currency = 'ETH';

        public string $quote_currency = 'USD';

        public string $open_price = '10';

        public string $close_price = '11';

        public string $size = '2';

        public string $direction = 'sell';

        public string $open_time = '2024-03-01 10:00:00';

        private string $secret = 'not a field';
    };

    expect(Trade::fromRow($arrayable)->pair())->toBe('BTC/USD')
        ->and(Trade::fromRow($object)->pair())->toBe('ETH/USD')
        ->and(Trade::fromRow($object)->direction)->toBe(Direction::SELL);
});

it('returns a trade given to fromRow unchanged', function (): void {
    $trade = Trade::fromArray(tradeRow());

    expect(Trade::fromRow($trade))->toBe($trade);
});

it('treats a null required field as missing', function (): void {
    expect(fn () => Trade::fromRow((object) (['open_price' => null] + tradeRow())))
        ->toThrow(InvalidTradeException::class, "missing the required 'open_price' field");
});

it('refuses a field of the wrong type with the package exception', function (string $field, mixed $value, string $message): void {
    expect(fn () => Trade::fromRow((object) ([$field => $value] + tradeRow())))
        ->toThrow(InvalidTradeException::class, $message);
})->with([
    'integer currency' => ['base_currency', 5, "'base_currency' field must be a string; got int"],
    'array price' => ['open_price', ['1'], "'open_price' field must be a number or numeric string; got array"],
    'integer-backed direction' => ['direction', 1, "'direction' field must be 'buy', 'sell' or a string-backed enum; got int"],
    'timestamp open time' => ['open_time', 1_709_287_200, "'open_time' field must be a date string or DateTimeInterface; got int"],
    'boolean commission' => ['commission', true, "'commission' field must be a number or numeric string; got bool"],
]);

// ---------------------------------------------------------------------------------------
// Builders as sources
// ---------------------------------------------------------------------------------------

it('calculates straight from an ordered query builder', function (): void {
    TradesTable::seed(orderSensitiveRows());

    $analytics = TradingAnalytics::calculate(DB::table('trades')->orderBy('close_time')->orderBy('id'));

    expect($analytics->toArray())->toBe(TradingAnalytics::calculate(orderSensitiveRows())->toArray())
        ->and($analytics->counts?->global->total->toInt())->toBe(5);
});

it('builds the engine from an ordered eloquent builder', function (): void {
    TradesTable::seed(orderSensitiveRows());

    $analytics = TradingAnalytics::for(TradeRecord::query()->orderBy('id'))->calculate();

    expect($analytics->toArray())->toBe(TradingAnalytics::calculate(orderSensitiveRows())->toArray());
});

it('streams an ordered relation, scoped to its parent', function (): void {
    $desk = TradingAccount::query()->create(['name' => 'desk']);
    $other = TradingAccount::query()->create(['name' => 'other']);

    TradesTable::seed(orderSensitiveRows(), $desk->id);
    TradesTable::seed([tradeRow(), tradeRow()], $other->id);

    $analytics = TradingAnalytics::calculate($desk->trades()->orderBy('open_time'));

    expect($analytics->counts?->global->total->toInt())->toBe(5)
        ->and($analytics->toArray())->toBe(TradingAnalytics::calculate(orderSensitiveRows())->toArray());
});

it('maps builder rows lazily through trades()', function (): void {
    TradesTable::seed(orderSensitiveRows());

    $trades = TradingAnalytics::trades(DB::table('trades')->orderBy('id'), chunk: 2);

    expect($trades)->toBeInstanceOf(LazyCollection::class)
        ->and($trades->all())->toHaveCount(5)->each->toBeInstanceOf(Trade::class);
});

it('follows the query order and refuses one that runs against close time', function (): void {
    TradesTable::seed(orderSensitiveRows());

    $chronological = TradingAnalytics::calculate(DB::table('trades')->orderBy('close_time'), only: [MaxDrawdown::class]);

    // Equity 10, 5, 105, 55, 75: the 50 drop from the 105 peak is 47.619…%.
    expect((string) $chronological->maxDrawdown?->percentage)->toBe('47.6190')
        ->and(fn () => TradingAnalytics::calculate(DB::table('trades')->orderByDesc('close_time'), only: [MaxDrawdown::class]))
        ->toThrow(UnorderedTradeSourceException::class, 'arrived after one closed at [2024-03-05 11:00:00]')
        ->and(fn () => TradingAnalytics::calculate(array_reverse(orderSensitiveRows()), only: [MaxDrawdown::class]))
        ->toThrow(UnorderedTradeSourceException::class);
});

it('refuses an unordered source before running a query', function (Closure $source): void {
    $source = $source();
    DB::enableQueryLog();

    expect(fn () => TradingAnalytics::calculate($source))
        ->toThrow(UnorderedTradeSourceException::class, "->orderBy('close_time')->orderBy('id')")
        ->and(DB::getQueryLog())->toBe([]);
})->with([
    'query builder' => fn () => DB::table('trades')->where('base_currency', 'BTC'),
    // Eloquent would silently order by the primary key; that is still a guess.
    'eloquent builder' => fn () => TradeRecord::query(),
    'relation' => fn () => TradingAccount::query()->create(['name' => 'desk'])->trades(),
]);

it('refuses an unordered source from every entry point', function (): void {
    $unordered = DB::table('trades');

    expect(fn () => TradingAnalytics::for($unordered))->toThrow(UnorderedTradeSourceException::class)
        ->and(fn () => TradingAnalytics::trades($unordered))->toThrow(UnorderedTradeSourceException::class)
        ->and(new UnorderedTradeSourceException)->toBeInstanceOf(TradingAnalyticsException::class);
});

it('pages the query with lazy(), never one unbounded select', function (): void {
    TradesTable::seed(orderSensitiveRows());
    DB::enableQueryLog();

    TradingAnalytics::calculate(DB::table('trades')->orderBy('id'), chunk: 2);

    $selects = array_column(DB::getQueryLog(), 'query');

    expect($selects)->toHaveCount(3)
        ->each->toContain('limit 2');
});

it('leaves the caller builder untouched', function (): void {
    TradesTable::seed(orderSensitiveRows());
    $query = DB::table('trades')->orderBy('id');

    TradingAnalytics::calculate($query, chunk: 2);

    expect($query->limit)->toBeNull()
        ->and($query->offset)->toBeNull()
        ->and($query->count())->toBe(5);
});

it('refuses a chunk size below one', function (int $chunk): void {
    expect(fn () => TradingAnalytics::calculate(DB::table('trades')->orderBy('id'), chunk: $chunk))
        ->toThrow(InvalidChunkSizeException::class, "at least 1; got {$chunk}");
})->with([0, -5]);

it('serves builder sources from the injected manager', function (): void {
    TradesTable::seed(orderSensitiveRows());

    $manager = app(TradingAnalyticsManager::class);

    expect($manager->calculate(DB::table('trades')->orderBy('id'), chunk: 3)->toArray())
        ->toBe(TradingAnalytics::calculate(orderSensitiveRows())->toArray());
});
