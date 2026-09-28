<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A host's trade model over the `trades` table, casting the columns the way an app would:
 * its own enum for the direction, immutable dates, fixed-point decimals.
 *
 * @property int $id
 */
final class TradeRecord extends Model
{
    protected $table = 'trades';

    protected $guarded = [];

    public $timestamps = false;

    /** @return BelongsTo<TradingAccount, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(TradingAccount::class, 'trading_account_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'direction' => Side::class,
            'open_price' => 'decimal:8',
            'close_price' => 'decimal:8',
            'size' => 'decimal:8',
            'commission' => 'decimal:8',
            'open_time' => 'immutable_datetime',
            'close_time' => 'datetime',
        ];
    }
}
