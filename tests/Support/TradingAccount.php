<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A host's account owning trades, so a `HasMany` relation can be passed as a trade source.
 */
final class TradingAccount extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    /** @return HasMany<TradeRecord, $this> */
    public function trades(): HasMany
    {
        return $this->hasMany(TradeRecord::class, 'trading_account_id');
    }
}
