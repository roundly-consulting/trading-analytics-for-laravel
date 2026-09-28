<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The host-side `trades` storage the builder-source tests read from. The package ships no
 * migration: this table belongs to the tests, in the column shape `Trade::fromRow()` reads.
 */
final class TradesTable
{
    public static function create(): void
    {
        Schema::create('trading_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('trades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trading_account_id')->nullable()->constrained('trading_accounts');
            $table->string('base_currency');
            $table->string('quote_currency');
            $table->decimal('open_price', 24, 8);
            $table->decimal('close_price', 24, 8);
            $table->decimal('size', 24, 8);
            $table->string('direction');
            $table->dateTime('open_time');
            $table->decimal('commission', 24, 8)->nullable();
            $table->dateTime('close_time')->nullable();
        });
    }

    /**
     * Insert rows in the `Trade::fromArray()` shape, in batches so a long stream never sits
     * in memory at once.
     *
     * @param  iterable<array<string, mixed>>  $rows
     */
    public static function seed(iterable $rows, ?int $accountId = null): void
    {
        $batch = [];

        foreach ($rows as $row) {
            $batch[] = ['trading_account_id' => $accountId, ...$row];

            if (count($batch) === 500) {
                DB::table('trades')->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('trades')->insert($batch);
        }
    }
}
